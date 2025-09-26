<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audio_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('voice_type')->default('standard');
            $table->string('voice_gender')->default('female');
            $table->float('voice_speed', 3, 2)->default(1.0);
            $table->float('voice_pitch', 3, 2)->default(0.0);
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->default('audio/mpeg');
            $table->timestamps();
            
            $table->index(['translation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};
