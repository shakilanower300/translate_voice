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

            Log::info('ElevenLabs: Starting speech generation', [
                'text_length' => strlen($text),
                'voice_id' => $voiceId,
                'stability' => $stability,
                'similarity_boost' => $similarityBoost
            ]);

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
                ],
                'timeout' => 30 // Add timeout to prevent hanging
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
            Log::error('Eleven Labs TTS generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'voice_id' => $voiceId ?? 'not_provided',
                'text_length' => strlen($text ?? ''),
                'api_key_configured' => !empty($this->apiKey)
            ]);
            
            return [
                'success' => false,
                'error' => 'Speech generation failed: ' . $e->getMessage(),
                'details' => [
                    'voice_id' => $voiceId ?? 'not_provided',
                    'api_configured' => !empty($this->apiKey)
                ]
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
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Spanish Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Spanish Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Spanish Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Spanish Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Spanish Female'
                ],
                'fr' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - French Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - French Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - French Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - French Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - French Female'
                ],
                'de' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - German Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - German Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - German Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - German Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - German Female'
                ],
                'it' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Italian Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Italian Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Italian Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Italian Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Italian Female'
                ],
                'pt' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Portuguese Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Portuguese Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Portuguese Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Portuguese Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Portuguese Female'
                ],
                'hi' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Hindi Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Hindi Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Hindi Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Hindi Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Hindi Female'
                ],
                'ja' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Japanese Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Japanese Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Japanese Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Japanese Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Japanese Female'
                ],
                'ko' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Korean Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Korean Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Korean Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Korean Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Korean Female'
                ],
                'zh' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Chinese Female',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Chinese Female',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Chinese Female',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Chinese Female',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Chinese Female'
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
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Spanish Male',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Spanish Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Spanish Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Spanish Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Spanish Male'
                ],
                'fr' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - French Male',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - French Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - French Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - French Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - French Male'
                ],
                'de' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - German Male',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - German Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - German Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - German Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - German Male'
                ],
                'it' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Italian Male',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Italian Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Italian Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Italian Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Italian Male'
                ],
                'pt' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Portuguese Male',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Portuguese Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Portuguese Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Portuguese Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Portuguese Male'
                ],
                'hi' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Hindi Male',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Hindi Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Hindi Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Hindi Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Hindi Male'
                ],
                'ja' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Japanese Male',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Japanese Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Japanese Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Japanese Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Japanese Male'
                ],
                'ko' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Korean Male',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Korean Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Korean Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Korean Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Korean Male'
                ],
                'zh' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Chinese Male',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Chinese Male',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Chinese Male',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Chinese Male',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Chinese Male'
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