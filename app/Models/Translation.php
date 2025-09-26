<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Translation extends Model
{
    protected $fillable = [
        'original_text',
        'source_language',
        'target_language',
        'translated_text',
        'ip_address'
    ];

    public function audioFiles(): HasMany
    {
        return $this->hasMany(AudioFile::class);
    }

    public function getLanguageName(string $code): string
    {
        $languages = [
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

        return $languages[$code] ?? $code;
    }
}
