<?php

namespace App\Services;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Exception;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    private $translator;

    public function __construct()
    {
        $this->translator = new GoogleTranslate();
    }

    /**
     * Get supported languages
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
            'zh' => 'Chinese (Simplified)',
            'ar' => 'Arabic',
            'hi' => 'Hindi',
            'nl' => 'Dutch',
            'sv' => 'Swedish',
            'da' => 'Danish',
            'no' => 'Norwegian',
            'pl' => 'Polish',
            'cs' => 'Czech',
            'sk' => 'Slovak',
            'hu' => 'Hungarian',
            'ro' => 'Romanian',
            'bg' => 'Bulgarian',
            'hr' => 'Croatian',
            'sr' => 'Serbian',
            'sl' => 'Slovenian',
            'et' => 'Estonian',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'fi' => 'Finnish',
            'tr' => 'Turkish',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'th' => 'Thai',
            'vi' => 'Vietnamese',
            'id' => 'Indonesian',
            'ms' => 'Malay',
            'tl' => 'Filipino',
            'sw' => 'Swahili',
            'uk' => 'Ukrainian',
            'be' => 'Belarusian',
            'ka' => 'Georgian',
            'am' => 'Amharic',
            'bn' => 'Bengali',
            'gu' => 'Gujarati',
            'kn' => 'Kannada',
            'ml' => 'Malayalam',
            'mr' => 'Marathi',
            'ne' => 'Nepali',
            'or' => 'Odia',
            'pa' => 'Punjabi',
            'si' => 'Sinhala',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'ur' => 'Urdu'
        ];
    }

    /**
     * Translate text to target language
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'auto'): array
    {
        try {
            $this->translator->setSource($sourceLanguage);
            $this->translator->setTarget($targetLanguage);

            $translatedText = $this->translator->translate($text);

            // If source was auto, try to detect the actual source language
            if ($sourceLanguage === 'auto') {
                try {
                    $detectedLang = $this->translator->getLastDetectedSource();
                    if ($detectedLang) {
                        $sourceLanguage = $detectedLang;
                    } else {
                        $sourceLanguage = 'en'; // fallback
                    }
                } catch (Exception $e) {
                    $sourceLanguage = 'en'; // fallback
                    Log::warning('Failed to detect source language: ' . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'original_text' => $text,
                'translated_text' => $translatedText,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'detected_source' => $sourceLanguage !== 'auto' ? $sourceLanguage : null
            ];
        } catch (Exception $e) {
            Log::error('Translation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Translation failed. Please try again.',
                'original_text' => $text,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage
            ];
        }
    }

    /**
     * Detect the language of given text
     */
    public function detectLanguage(string $text): string
    {
        try {
            $this->translator->setSource('auto');
            $this->translator->translate($text);
            $detected = $this->translator->getLastDetectedSource();
            
            return $detected ?: 'en';
        } catch (Exception $e) {
            Log::error('Language detection failed: ' . $e->getMessage());
            return 'en'; // fallback to English
        }
    }

    /**
     * Batch translate multiple texts
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'auto'): array
    {
        $results = [];
        
        foreach ($texts as $text) {
            $results[] = $this->translate($text, $targetLanguage, $sourceLanguage);
        }
        
        return $results;
    }

    /**
     * Get popular language combinations
     */
    public function getPopularLanguages(): array
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
            'zh' => 'Chinese',
            'ar' => 'Arabic',
            'hi' => 'Hindi'
        ];
    }

    /**
     * Validate if language code is supported
     */
    public function isLanguageSupported(string $languageCode): bool
    {
        return array_key_exists($languageCode, $this->getSupportedLanguages());
    }
}