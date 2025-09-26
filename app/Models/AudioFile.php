<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioFile extends Model
{
    protected $fillable = [
        'translation_id',
        'file_path',
        'file_name',
        'voice_type',
        'voice_gender',
        'voice_speed',
        'voice_pitch',
        'file_size',
        'mime_type'
    ];

    protected $casts = [
        'voice_speed' => 'float',
        'voice_pitch' => 'float',
        'file_size' => 'integer'
    ];

    public function translation(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        
        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unit];
    }
}
