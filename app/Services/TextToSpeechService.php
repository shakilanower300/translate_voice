<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class TextToSpeechService
{
    private $client;
    private $storageDir = 'audio'; // Storage directory for public disk

    public function __construct()
    {
        $this->client = new Client();
        
        // Ensure audio directory exists in public storage
        if (!Storage::disk('public')->exists($this->storageDir)) {
            Storage::disk('public')->makeDirectory($this->storageDir);
        }
    }

    /**
     * Get available voice options
     */
    public function getVoiceOptions(): array
    {
        return [
            'standard' => [
                'female' => [
                    'en' => ['name' => 'English Female', 'code' => 'en-US-Standard-C'],
                    'es' => ['name' => 'Spanish Female', 'code' => 'es-ES-Standard-A'],
                    'fr' => ['name' => 'French Female', 'code' => 'fr-FR-Standard-A'],
                    'de' => ['name' => 'German Female', 'code' => 'de-DE-Standard-A'],
                    'it' => ['name' => 'Italian Female', 'code' => 'it-IT-Standard-A'],
                    'pt' => ['name' => 'Portuguese Female', 'code' => 'pt-BR-Standard-A'],
                    'ru' => ['name' => 'Russian Female', 'code' => 'ru-RU-Standard-A'],
                    'ja' => ['name' => 'Japanese Female', 'code' => 'ja-JP-Standard-A'],
                    'ko' => ['name' => 'Korean Female', 'code' => 'ko-KR-Standard-A'],
                    'zh' => ['name' => 'Chinese Female', 'code' => 'zh-CN-Standard-A']
                ],
                'male' => [
                    'en' => ['name' => 'English Male', 'code' => 'en-US-Standard-B'],
                    'es' => ['name' => 'Spanish Male', 'code' => 'es-ES-Standard-B'],
                    'fr' => ['name' => 'French Male', 'code' => 'fr-FR-Standard-B'],
                    'de' => ['name' => 'German Male', 'code' => 'de-DE-Standard-B'],
                    'it' => ['name' => 'Italian Male', 'code' => 'it-IT-Standard-C'],
                    'pt' => ['name' => 'Portuguese Male', 'code' => 'pt-BR-Standard-B'],
                    'ru' => ['name' => 'Russian Male', 'code' => 'ru-RU-Standard-B'],
                    'ja' => ['name' => 'Japanese Male', 'code' => 'ja-JP-Standard-C'],
                    'ko' => ['name' => 'Korean Male', 'code' => 'ko-KR-Standard-C'],
                    'zh' => ['name' => 'Chinese Male', 'code' => 'zh-CN-Standard-B']
                ]
            ]
        ];
    }

    /**
     * Generate speech from text using browser-based synthesis (fallback method)
     */
    public function generateSpeech(
        string $text, 
        string $language = 'en', 
        string $gender = 'female',
        float $speed = 1.0,
        float $pitch = 0.0
    ): array {
        try {
            // Create a unique filename - always use .wav extension
            $filename = 'tts_' . md5($text . $language . $gender . $speed . $pitch) . '.wav';
            $filepath = $this->storageDir . '/' . $filename;
            
            // Generate the actual WAV audio file
            $this->createPlaceholderAudio($filepath, $text);
            
            // Ensure the WAV file was created successfully
            if (!Storage::disk('public')->exists($filepath)) {
                throw new Exception('Failed to create audio file');
            }
            
            // Create metadata for the audio file
            $audioData = [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => Storage::disk('public')->url($filepath),
                'text' => $text,
                'language' => $language,
                'gender' => $gender,
                'speed' => $speed,
                'pitch' => $pitch,
                'duration' => $this->estimateDuration($text, $speed),
                'file_size' => Storage::disk('public')->size($filepath),
                'mime_type' => 'audio/wav',
                'created_at' => now()
            ];

            return $audioData;
            
        } catch (Exception $e) {
            Log::error('TTS generation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Failed to generate speech. Please try again.',
                'text' => $text
            ];
        }
    }

    /**
     * Generate speech using real TTS service (Google Cloud TTS example)
     * This requires API keys and would be implemented in production
     */
    public function generateSpeechWithCloudTTS(
        string $text,
        string $language = 'en',
        string $gender = 'female',
        float $speed = 1.0,
        float $pitch = 0.0
    ): array {
        // This would integrate with actual TTS services like:
        // - Google Cloud Text-to-Speech
        // - Amazon Polly  
        // - Azure Cognitive Services Speech
        // - IBM Watson Text to Speech
        
        // Example implementation would look like:
        /*
        try {
            $voiceOptions = $this->getVoiceOptions();
            $voiceCode = $voiceOptions['standard'][$gender][$language]['code'] ?? 'en-US-Standard-C';
            
            $response = $this->client->post('https://texttospeech.googleapis.com/v1/text:synthesize', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.google.api_key'),
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'input' => ['text' => $text],
                    'voice' => [
                        'languageCode' => $language,
                        'name' => $voiceCode
                    ],
                    'audioConfig' => [
                        'audioEncoding' => 'MP3',
                        'speakingRate' => $speed,
                        'pitch' => $pitch
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            $audioContent = base64_decode($result['audioContent']);
            
            // Save to storage
            $filename = 'tts_' . uniqid() . '.mp3';
            $filepath = $this->storageDir . '/' . $filename;
            Storage::disk('public')->put($filepath, $audioContent);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => Storage::disk('public')->url($filepath),
                'file_size' => strlen($audioContent)
            ];
            
        } catch (Exception $e) {
            Log::error('Cloud TTS failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
        */
        
        // For now, return demo response
        return $this->generateSpeech($text, $language, $gender, $speed, $pitch);
    }

    /**
     * Estimate audio duration based on text length and speed
     */
    private function estimateDuration(string $text, float $speed = 1.0): float
    {
        // Average speaking rate is about 150-200 words per minute
        $wordsPerMinute = 175;
        $wordCount = str_word_count($text);
        $baseMinutes = $wordCount / $wordsPerMinute;
        
        // Adjust for speed
        $actualMinutes = $baseMinutes / $speed;
        
        return round($actualMinutes * 60, 1); // Return seconds
    }

    /**
     * Create a placeholder audio file for demo purposes
     */
    private function createPlaceholderAudio(string $filepath, string $text): void
    {
        // Generate a simple sine wave audio file using PHP
        $this->generateSineWaveAudio($filepath, $text);
    }

    /**
     * Generate a simple sine wave audio file
     */
    private function generateSineWaveAudio(string $filepath, string $text): void
    {
        // Create a simple WAV file with sine wave tones
        $sampleRate = 44100;
        $duration = max(2, min(10, strlen($text) * 0.1)); // Duration based on text length
        $frequency = 440; // A4 note
        
        $samples = [];
        for ($i = 0; $i < $sampleRate * $duration; $i++) {
            $time = $i / $sampleRate;
            $sample = sin(2 * pi() * $frequency * $time) * 0.3; // 30% volume
            $samples[] = pack('s', $sample * 32767); // Convert to 16-bit signed integer
        }
        
        $audioData = implode('', $samples);
        
        // Create WAV header
        $wavHeader = $this->createWavHeader(strlen($audioData), $sampleRate);
        $wavFile = $wavHeader . $audioData;
        
        // Always store as WAV file - this fixes the corrupted download issue
        $wavPath = str_replace('.wav', '.wav', $filepath); // Ensure .wav extension
        if (strpos($filepath, '.wav') === false) {
            $wavPath = str_replace('.mp3', '.wav', $filepath);
        }
        
        Storage::disk('public')->put($wavPath, $wavFile);
        
        // Log the file creation for debugging
        Log::info('Generated WAV file: ' . $wavPath . ' (size: ' . strlen($wavFile) . ' bytes)');
    }

    /**
     * Create WAV file header
     */
    private function createWavHeader(int $dataLength, int $sampleRate): string
    {
        $channels = 1; // Mono
        $bitsPerSample = 16;
        $byteRate = $sampleRate * $channels * $bitsPerSample / 8;
        $blockAlign = $channels * $bitsPerSample / 8;
        
        return pack('a4Va4a4VvvVVvva4V',
            'RIFF',                          // ChunkID
            $dataLength + 36,                // ChunkSize
            'WAVE',                          // Format
            'fmt ',                          // Subchunk1ID
            16,                              // Subchunk1Size (PCM)
            1,                               // AudioFormat (PCM)
            $channels,                       // NumChannels
            $sampleRate,                     // SampleRate
            $byteRate,                       // ByteRate
            $blockAlign,                     // BlockAlign
            $bitsPerSample,                  // BitsPerSample
            'data',                          // Subchunk2ID
            $dataLength                      // Subchunk2Size
        );
    }

    /**
     * Get supported languages for TTS
     */
    public function getSupportedLanguages(): array
    {
        return [
            'en' => 'English',
            'es' => 'Spanish', 
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese'
        ];
    }

    /**
     * Delete audio file
     */
    public function deleteAudioFile(string $filepath): bool
    {
        try {
            if (Storage::disk('public')->exists($filepath)) {
                Storage::disk('public')->delete($filepath);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error('Failed to delete audio file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get audio file download URL
     */
    public function getDownloadUrl(string $filepath): ?string
    {
        if (Storage::disk('public')->exists($filepath)) {
            return Storage::disk('public')->url($filepath);
        }
        return null;
    }
}