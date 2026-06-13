<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 32)->default('tutor');
            $table->string('scenario_id')->nullable();
            $table->string('hsk_level', 8)->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->foreign('scenario_id')->references('id')->on('roleplay_scenarios')->nullOnDelete();
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id');
            $table->string('role', 16);
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('ai_chat_sessions')->cascadeOnDelete();
        });

        Schema::create('pronunciation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('word_id')->nullable();
            $table->string('target_text');
            $table->string('transcript')->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->json('feedback')->nullable();
            $table->string('audio_path')->nullable();
            $table->timestamps();

            $table->foreign('word_id')->references('id')->on('words')->nullOnDelete();
        });

        Schema::create('review_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 64);
            $table->string('source', 32)->default('review-code');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_events');
        Schema::dropIfExists('pronunciation_attempts');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
    }
};
