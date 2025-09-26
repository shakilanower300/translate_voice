<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ElevenLabsService
{
    private $client;
    private $apiKey;
    private $baseUrl = 'https://api.elevenlabs.io/v1';
    private $storageDir = 'audio';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('ELEVEN_LABS_API_KEY');
        
        // Ensure audio directory exists
        if (!Storage::disk('public')->exists($this->storageDir)) {
            Storage::disk('public')->makeDirectory($this->storageDir);
        }
    }

    /**
     * Get available voices from Eleven Labs
     */
    public function getVoices(): array
    {
        try {
            if (!$this->apiKey) {
                throw new Exception('Eleven Labs API key not configured');
            }

            $response = $this->client->get($this->baseUrl . '/voices', [
                'headers' => [
                    'Accept' => 'application/json',
                    'xi-api-key' => $this->apiKey
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            return [
                'success' => true,
                'voices' => $data['voices'] ?? []
            ];
        } catch (Exception $e) {
            Log::error('Failed to get Eleven Labs voices: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Failed to get voices: ' . $e->getMessage(),
                'voices' => []
            ];
        }
    }

    /**
     * Generate speech using Eleven Labs API
     */
    public function generateSpeech(
        string $text,
        string $voiceId = null,
        float $stability = 0.5,
        float $similarityBoost = 0.5,
        float $style = 0.0,
        bool $useSpeakerBoost = true
    ): array {
        try {
            if (!$this->apiKey) {
                throw new Exception('Eleven Labs API key not configured');
            }

            // Use default voice if none specified
            if (!$voiceId) {
                $voiceId = 'EXAVITQu4vr4xnSDxMaL'; // Bella voice ID (default female voice)
            }

            $response = $this->client->post($this->baseUrl . '/text-to-speech/' . $voiceId, [
                'headers' => [
                    'Accept' => 'audio/mpeg',
                    'Content-Type' => 'application/json',
                    'xi-api-key' => $this->apiKey
                ],
                'json' => [
                    'text' => $text,
                    'model_id' => 'eleven_monolingual_v1',
                    'voice_settings' => [
                        'stability' => $stability,
                        'similarity_boost' => $similarityBoost,
                        'style' => $style,
                        'use_speaker_boost' => $useSpeakerBoost
                    ]
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Eleven Labs API returned status: ' . $response->getStatusCode());
            }

            $audioContent = $response->getBody()->getContents();
            
            // Create unique filename
            $filename = 'elevenlabs_' . md5($text . $voiceId . time()) . '.mp3';
            $filepath = $this->storageDir . '/' . $filename;
            
            // Debug: Log the storage path
            Log::info('ElevenLabs: Storing file at path: ' . $filepath);
            Log::info('ElevenLabs: Full storage path: ' . storage_path('app/public/' . $filepath));
            
            // Save the audio file
            Storage::disk('public')->put($filepath, $audioContent);
            
            // Verify file was created
            if (Storage::disk('public')->exists($filepath)) {
                Log::info('ElevenLabs: File created successfully at: ' . $filepath);
                Log::info('ElevenLabs: File size: ' . Storage::disk('public')->size($filepath) . ' bytes');
            } else {
                Log::error('ElevenLabs: Failed to create file at: ' . $filepath);
            }
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => Storage::disk('public')->url($filepath),
                'file_size' => strlen($audioContent),
                'mime_type' => 'audio/mpeg',
                'voice_id' => $voiceId,
                'text' => $text
            ];

        } catch (Exception $e) {
            Log::error('Eleven Labs TTS generation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Speech generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get default voice IDs for different genders and languages
     */
    public function getDefaultVoices(): array
    {
        return [
            'female' => [
                'en' => [
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - American, Young Adult',
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - English, Seductive',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - American, Young Adult',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - American, Young Adult',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - British, Middle Aged'
                ],
                'es' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual'
                ],
                'fr' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual'
                ],
                'de' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual'
                ],
                'it' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual'
                ],
                'pt' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual'
                ]
            ],
            'male' => [
                'en' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - American, Deep',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - American, Hoarse',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - American, Crisp',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - American, Raspy',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - British, Conversational'
                ],
                'es' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual'
                ],
                'fr' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual'
                ],
                'de' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual'
                ],
                'it' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual'
                ],
                'pt' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual'
                ]
            ]
        ];
    }

    /**
     * Get available voice options for a specific language and gender
     */
    public function getVoiceOptions(string $language, string $gender): array
    {
        $defaultVoices = $this->getDefaultVoices();
        
        if (isset($defaultVoices[$gender][$language])) {
            return $defaultVoices[$gender][$language];
        }
        
        // Fallback to English voices
        if (isset($defaultVoices[$gender]['en'])) {
            return $defaultVoices[$gender]['en'];
        }
        
        return [];
    }

    /**
     * Get voice ID based on gender and language (returns first available voice)
     */
    public function getVoiceForLanguageAndGender(string $language, string $gender): string
    {
        $voiceOptions = $this->getVoiceOptions($language, $gender);
        
        if (!empty($voiceOptions)) {
            // Return the first voice ID (array key)
            return array_keys($voiceOptions)[0];
        }
        
        // Ultimate fallback to Bella (female voice)
        return 'EXAVITQu4vr4xnSDxMaL';
    }

    /**
     * Get voice ID by specific voice selection
     */
    public function getVoiceById(string $voiceId): string
    {
        return $voiceId;
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}