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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->text('original_text');
            $table->string('source_language', 10)->default('en');
            $table->string('target_language', 10);
            $table->text('translated_text');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index(['target_language', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
