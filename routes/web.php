<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TextToSpeechController;

// Health check route for Railway
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'php_version' => PHP_VERSION
    ]);
});

// Main application route
Route::get('/', [TextToSpeechController::class, 'index'])->name('home');

// API routes
Route::prefix('api')->group(function () {
    Route::post('/translate', [TextToSpeechController::class, 'translate'])->name('api.translate');
    Route::post('/generate-speech', [TextToSpeechController::class, 'generateSpeech'])->name('api.generate-speech');
    Route::get('/history', [TextToSpeechController::class, 'getHistory'])->name('api.history');
    Route::get('/languages', [TextToSpeechController::class, 'getSupportedLanguages'])->name('api.languages');
    Route::get('/voice-options', [TextToSpeechController::class, 'getVoiceOptions'])->name('api.voice-options');
    Route::get('/download-audio/{audioFileId}', [TextToSpeechController::class, 'downloadAudio'])->name('api.download-audio');
    Route::delete('/translation/{translationId}', [TextToSpeechController::class, 'deleteTranslation'])->name('api.delete-translation');
});
