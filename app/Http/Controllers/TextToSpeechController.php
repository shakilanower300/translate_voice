<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\AudioFile;
use App\Services\TranslationService;
use App\Services\TextToSpeechService;
use App\Services\ElevenLabsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TextToSpeechController extends Controller
{
    protected $translationService;
    protected $ttsService;
    protected $elevenLabsService;

    public function __construct(
        TranslationService $translationService, 
        TextToSpeechService $ttsService,
        ElevenLabsService $elevenLabsService
    ) {
        $this->translationService = $translationService;
        $this->ttsService = $ttsService;
        $this->elevenLabsService = $elevenLabsService;
    }

    /**
     * Show the main application page
     */
    public function index(): View
    {
        try {
            $supportedLanguages = $this->translationService->getSupportedLanguages();
            $popularLanguages = $this->translationService->getPopularLanguages();
            $voiceOptions = $this->ttsService->getVoiceOptions();
        } catch (\Throwable $e) {
            // Fallback data if services fail
            $supportedLanguages = [
                'en' => 'English',
                'es' => 'Spanish',
                'fr' => 'French',
                'de' => 'German',
                'it' => 'Italian'
            ];
            $popularLanguages = $supportedLanguages;
            $voiceOptions = [];
        }

        // Get recent translations for history without failing the page if the DB is unavailable
        try {
            $recentTranslations = Translation::with('audioFiles')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            Log::error('Failed to load translation history for landing page', [
                'message' => $e->getMessage(),
            ]);
            $recentTranslations = collect();
        }

        return view('text-to-speech', compact(
            'supportedLanguages', 
            'popularLanguages', 
            'voiceOptions',
            'recentTranslations'
        ));
    }

    /**
     * Translate text
     */
    public function translate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:5000',
            'target_language' => 'required|string|size:2',
            'source_language' => 'nullable|string|max:4' // Allow "auto" (4 chars) or language codes (2 chars)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $text = $request->input('text');
            $targetLanguage = $request->input('target_language');
            $sourceLanguage = $request->input('source_language', 'auto');

            // Validate supported languages (skip validation for 'auto')
            if ($sourceLanguage !== 'auto' && !$this->translationService->isLanguageSupported($sourceLanguage)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Source language not supported'
                ], 400);
            }
            
            if (!$this->translationService->isLanguageSupported($targetLanguage)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Target language not supported'
                ], 400);
            }

            $result = $this->translationService->translate($text, $targetLanguage, $sourceLanguage);

            if ($result['success']) {
                try {
                    // Attempt to persist translation history (non-critical)
                    $translation = Translation::create([
                        'original_text' => $result['original_text'],
                        'source_language' => $result['source_language'],
                        'target_language' => $result['target_language'],
                        'translated_text' => $result['translated_text'],
                        'ip_address' => $request->ip()
                    ]);

                    $result['translation_id'] = $translation->id;
                } catch (\Throwable $dbException) {
                    Log::warning('Translation saved without database persistence', [
                        'error' => $dbException->getMessage()
                    ]);

                    $result['translation_id'] = null;
                    $result['storage_persisted'] = false;
                    $result['message'] = $result['message'] ?? 'Translation succeeded but history is unavailable right now.';
                }
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Translation failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate speech from text
     */
    public function generateSpeech(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:5000',
            'language' => 'required|string|size:2',
            'gender' => 'required|in:male,female',
            'speed' => 'nullable|numeric|min:0.25|max:2.0',
            'pitch' => 'nullable|numeric|min:-20.0|max:20.0',
            'translation_id' => 'nullable|integer|exists:translations,id',
            'use_elevenlabs' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $text = $request->input('text');
            $language = $request->input('language');
            $gender = $request->input('gender');
            $speed = $request->input('speed', 1.0);
            $pitch = $request->input('pitch', 0.0);
            $translationId = $request->input('translation_id');
            $useElevenLabs = $request->input('use_elevenlabs', false);
            $voiceId = $request->input('voice_id'); // New voice selection parameter

            $result = null;
            
            Log::info('Speech generation request', [
                'use_elevenlabs' => $useElevenLabs,
                'elevenlabs_configured' => $this->elevenLabsService->isConfigured(),
                'language' => $language,
                'gender' => $gender
            ]);
            
            if ($useElevenLabs && $this->elevenLabsService->isConfigured()) {
                // Use Eleven Labs API
                $selectedVoiceId = $voiceId ?: $this->elevenLabsService->getVoiceForLanguageAndGender($language, $gender);
                
                // Map speed to ElevenLabs parameters (stability and similarity_boost)
                $stability = 0.5;
                $similarityBoost = max(0.1, min(1.0, $speed));
                
                $result = $this->elevenLabsService->generateSpeech(
                    $text,
                    $selectedVoiceId,
                    $stability,
                    $similarityBoost
                );
                
                if ($result['success']) {
                    $result['provider'] = 'elevenlabs';
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Eleven Labs speech generation failed. Please try again.'
                    ], 500);
                }
            } else {
                // For basic TTS, use Web Speech API for playback
                $result = [
                    'success' => true,
                    'provider' => 'webspeech',
                    'message' => 'Using Web Speech API for playback',
                    'use_web_speech' => true,
                    'text' => $text,
                    'language' => $language,
                    'gender' => $gender,
                    'speed' => $speed,
                    'pitch' => $pitch
                ];
                
                // Don't create placeholder audio files - just use Web Speech API
                Log::info('Using Web Speech API for TTS', [
                    'text_length' => strlen($text),
                    'language' => $language,
                    'gender' => $gender
                ]);
            }

            if ($result['success'] && $translationId && $result['provider'] === 'elevenlabs' && isset($result['filepath'])) {
                try {
                    // Save audio file record to database (for Eleven Labs only)
                    $audioFile = AudioFile::create([
                        'translation_id' => $translationId,
                        'file_path' => $result['filepath'],
                        'file_name' => $result['filename'],
                        'voice_type' => $result['provider'] ?? 'elevenlabs',
                        'voice_gender' => $gender,
                        'voice_speed' => $speed,
                        'voice_pitch' => $pitch,
                        'file_size' => $result['file_size'],
                        'mime_type' => $result['mime_type']
                    ]);

                    $result['audio_file_id'] = $audioFile->id;
                } catch (\Throwable $dbException) {
                    Log::warning('Audio metadata not persisted', [
                        'error' => $dbException->getMessage(),
                        'translation_id' => $translationId
                    ]);

                    $result['audio_file_id'] = null;
                    $result['storage_persisted'] = false;
                }
            }
            // Note: Web Speech API doesn't create downloadable files, so no database record

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Speech generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Speech generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get translation history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 20), 50);

        try {
            $translations = Translation::with('audioFiles')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $translations->items(),
                'meta' => [
                    'current_page' => $translations->currentPage(),
                    'per_page' => $translations->perPage(),
                    'total' => $translations->total(),
                    'last_page' => $translations->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            Log::warning('History unavailable', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $perPage ?? 20,
                    'total' => 0,
                    'last_page' => 1,
                    'storage_persisted' => false
                ],
                'message' => 'History is temporarily unavailable while the database is offline.'
            ]);
        }
    }

    /**
     * Get available voice options for Eleven Labs
     */
    public function getVoiceOptions(Request $request)
    {
        try {
            $language = $request->query('language', 'en');
            $gender = $request->query('gender', 'female');
            
            $voiceOptions = $this->elevenLabsService->getVoiceOptions($language, $gender);
            
            // Transform the voice options into the expected format
            $voices = [];
            foreach ($voiceOptions as $voiceId => $voiceDescription) {
                $voices[] = [
                    'id' => $voiceId,
                    'description' => $voiceDescription
                ];
            }
            
            return response()->json([
                'success' => true,
                'voices' => $voices
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting voice options: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting voice options'
            ], 500);
        }
    }

    /**
     * Download audio file
     */
    public function downloadAudio(Request $request, int $audioFileId)
    {
        try {
            $audioFile = AudioFile::findOrFail($audioFileId);
            
            if (!Storage::disk('public')->exists($audioFile->file_path)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Audio file not found'
                ], 404);
            }

            return Storage::disk('public')->download($audioFile->file_path, $audioFile->file_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Download failed'
            ], 500);
        }
    }

    /**
     * Delete translation and associated audio files
     */
    public function deleteTranslation(Request $request, int $translationId): JsonResponse
    {
        try {
            $translation = Translation::with('audioFiles')->findOrFail($translationId);
            
            // Delete associated audio files from storage
            foreach ($translation->audioFiles as $audioFile) {
                if (Storage::disk('public')->exists($audioFile->file_path)) {
                    Storage::disk('public')->delete($audioFile->file_path);
                }
            }
            
            // Delete from database (cascade will handle audio_files table)
            $translation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Translation deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete translation'
            ], 500);
        }
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'languages' => $this->translationService->getSupportedLanguages(),
            'popular' => $this->translationService->getPopularLanguages(),
            'tts_languages' => $this->ttsService->getSupportedLanguages()
        ]);
    }
}
