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
                    'model_id' => 'eleven_multilingual_v2', // Use multilingual model for better language support
                    'voice_settings' => [
                        'stability' => $stability,
                        'similarity_boost' => $similarityBoost,
                        'style' => $style,
                        'use_speaker_boost' => $useSpeakerBoost
                    ]
                ]
            ]);

            Log::info('ElevenLabs API request sent', [
                'voice_id' => $voiceId,
                'model' => 'eleven_multilingual_v2',
                'text_length' => strlen($text),
                'stability' => $stability,
                'similarity_boost' => $similarityBoost
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
                'voice_id' => $voiceId ?? 'unknown',
                'text_length' => strlen($text ?? ''),
                'api_key_configured' => !empty($this->apiKey)
            ]);
            
            return [
                'success' => false,
                'error' => 'Speech generation failed: ' . $e->getMessage(),
                'details' => [
                    'voice_id' => $voiceId ?? 'unknown',
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
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual'
                ],
                'fr' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual'
                ],
                'de' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual'
                ],
                'it' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual'
                ],
                'pt' => [
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual',
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual'
                ],
                'hi' => [
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual Hindi',
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual Hindi',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual Hindi',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual Hindi',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual Hindi'
                ],
                'ja' => [
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual Japanese',
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual Japanese',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual Japanese',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual Japanese',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual Japanese'
                ],
                'zh' => [
                    'EXAVITQu4vr4xnSDxMaL' => 'Bella - Multilingual Chinese',
                    'XB0fDUnXU5powFXDhCwa' => 'Charlotte - Multilingual Chinese',
                    'IKne3meq5aSn9XLyUdCD' => 'Freya - Multilingual Chinese',
                    'jBpfuIE2acCO8z3wKNLl' => 'Gigi - Multilingual Chinese',
                    'N2lVS1w4EtoT3dr4eOWO' => 'Lily - Multilingual Chinese'
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
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual'
                ],
                'fr' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual'
                ],
                'de' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual'
                ],
                'it' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual'
                ],
                'pt' => [
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual',
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual'
                ],
                'hi' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual Hindi',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual Hindi',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual Hindi',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual Hindi',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual Hindi'
                ],
                'ja' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual Japanese',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual Japanese',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual Japanese',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual Japanese',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual Japanese'
                ],
                'zh' => [
                    'pNInz6obpgDQGcFmaJgB' => 'Adam - Multilingual Chinese',
                    '5Q0t7uMcjvnagumLfvZi' => 'Callum - Multilingual Chinese',
                    'VR6AewLTigWG4xSOukaG' => 'Arnold - Multilingual Chinese',
                    'yoZ06aMxZJJ28mfd3POQ' => 'Sam - Multilingual Chinese',
                    'CYw3kZ02Hs0563khs1Fj' => 'Dave - Multilingual Chinese'
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
        
        Log::info('ElevenLabs: Getting voice options', [
            'language' => $language,
            'gender' => $gender,
            'available_genders' => array_keys($defaultVoices),
            'available_languages' => isset($defaultVoices[$gender]) ? array_keys($defaultVoices[$gender]) : []
        ]);
        
        if (isset($defaultVoices[$gender][$language])) {
            Log::info('ElevenLabs: Found voices for language', [
                'count' => count($defaultVoices[$gender][$language])
            ]);
            return $defaultVoices[$gender][$language];
        }
        
        // Fallback to English voices
        if (isset($defaultVoices[$gender]['en'])) {
            Log::info('ElevenLabs: Using English fallback voices for unsupported language', [
                'requested_language' => $language,
                'count' => count($defaultVoices[$gender]['en'])
            ]);
            return $defaultVoices[$gender]['en'];
        }
        
        // Ultimate fallback - get first available gender/language combination
        if (!empty($defaultVoices)) {
            $firstGender = array_keys($defaultVoices)[0];
            if (!empty($defaultVoices[$firstGender])) {
                $firstLanguage = array_keys($defaultVoices[$firstGender])[0];
                Log::warning('ElevenLabs: Using ultimate fallback voices', [
                    'requested_gender' => $gender,
                    'requested_language' => $language,
                    'fallback_gender' => $firstGender,
                    'fallback_language' => $firstLanguage
                ]);
                return $defaultVoices[$firstGender][$firstLanguage];
            }
        }
        
        Log::error('ElevenLabs: No voices available', [
            'gender' => $gender,
            'language' => $language
        ]);
        
        return [];
    }

    /**
     * Get voice ID based on gender and language (returns first available voice)
     */
    public function getVoiceForLanguageAndGender(string $language, string $gender): string
    {
        Log::info('ElevenLabs: Getting voice for language and gender', [
            'language' => $language,
            'gender' => $gender
        ]);
        
        $voiceOptions = $this->getVoiceOptions($language, $gender);
        
        if (!empty($voiceOptions)) {
            // Return the first voice ID (array key)
            $voiceId = array_keys($voiceOptions)[0];
            Log::info('ElevenLabs: Selected voice', [
                'voice_id' => $voiceId,
                'voice_name' => $voiceOptions[$voiceId] ?? 'Unknown'
            ]);
            return $voiceId;
        }
        
        // Ultimate fallback to Bella (female voice)
        Log::warning('ElevenLabs: Using ultimate fallback voice (Bella)', [
            'requested_language' => $language,
            'requested_gender' => $gender
        ]);
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