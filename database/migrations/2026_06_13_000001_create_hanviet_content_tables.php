<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('color', 16);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('icon', 32)->nullable();
            $table->unsignedSmallInteger('lesson_count')->default(0);
            $table->timestamps();
        });

        Schema::create('words', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('hanzi');
            $table->string('pinyin')->nullable();
            $table->string('vietnamese')->nullable();
            $table->string('english')->nullable();
            $table->unsignedTinyInteger('hsk');
            $table->string('topic_id')->nullable();
            $table->json('example')->nullable();
            $table->timestamps();

            $table->unique('hanzi');
            $table->index(['hsk', 'topic_id']);
            $table->foreign('topic_id')->references('id')->on('topics')->nullOnDelete();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('level_id');
            $table->string('topic_id')->nullable();
            $table->string('title');
            $table->unsignedSmallInteger('duration')->default(15);
            $table->text('intro')->nullable();
            $table->json('skills')->nullable();
            $table->timestamps();

            $table->foreign('level_id')->references('id')->on('levels')->cascadeOnDelete();
            $table->foreign('topic_id')->references('id')->on('topics')->nullOnDelete();
        });

        Schema::create('lesson_dialogues', function (Blueprint $table) {
            $table->id();
            $table->string('lesson_id');
            $table->string('speaker', 8)->nullable();
            $table->string('hanzi');
            $table->string('pinyin')->nullable();
            $table->string('vietnamese')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('lesson_id')->references('id')->on('lessons')->cascadeOnDelete();
        });

        Schema::create('lesson_word', function (Blueprint $table) {
            $table->id();
            $table->string('lesson_id');
            $table->string('word_id');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->unique(['lesson_id', 'word_id']);
            $table->foreign('lesson_id')->references('id')->on('lessons')->cascadeOnDelete();
            $table->foreign('word_id')->references('id')->on('words')->cascadeOnDelete();
        });

        Schema::create('dictionary_entries', function (Blueprint $table) {
            $table->id();
            $table->string('hanzi');
            $table->string('pinyin')->nullable();
            $table->string('vietnamese')->nullable();
            $table->unsignedTinyInteger('hsk')->nullable();
            $table->string('pos')->nullable();
            $table->json('examples')->nullable();
            $table->timestamps();

            $table->index('hanzi');
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('level_id')->nullable();
            $table->string('lesson_id')->nullable();
            $table->timestamps();

            $table->foreign('level_id')->references('id')->on('levels')->nullOnDelete();
            $table->foreign('lesson_id')->references('id')->on('lessons')->nullOnDelete();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->string('quiz_id');
            $table->string('external_id')->nullable();
            $table->string('type', 32);
            $table->text('question');
            $table->string('hanzi')->nullable();
            $table->string('audio_text')->nullable();
            $table->json('options');
            $table->unsignedTinyInteger('correct_index')->default(0);
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
        });

        Schema::create('video_playlists', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('source', 32)->default('youtube');
            $table->string('playlist_id')->nullable();
            $table->string('playlist_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('premium')->default(false);
            $table->boolean('embed_playlist')->default(false);
            $table->timestamps();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('playlist_id')->nullable();
            $table->string('youtube_id');
            $table->string('title');
            $table->string('duration', 16)->nullable();
            $table->string('level_id')->nullable();
            $table->string('topic_id')->nullable();
            $table->boolean('free')->default(true);
            $table->boolean('has_subtitle')->default(false);
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->foreign('playlist_id')->references('id')->on('video_playlists')->nullOnDelete();
            $table->foreign('level_id')->references('id')->on('levels')->nullOnDelete();
            $table->foreign('topic_id')->references('id')->on('topics')->nullOnDelete();
        });

        Schema::create('featured_playlists', function (Blueprint $table) {
            $table->id();
            $table->string('playlist_id');
            $table->string('title');
            $table->string('embed_url');
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('premium_plans', function (Blueprint $table) {
            $table->string('slug')->primary();
            $table->unsignedInteger('amount');
            $table->string('currency', 8)->default('VND');
            $table->string('label');
            $table->string('savings')->nullable();
            $table->timestamps();
        });

        Schema::create('premium_features', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('icon', 16)->nullable();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->json('highlights')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('roleplay_scenarios', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('level_id')->nullable();
            $table->timestamps();

            $table->foreign('level_id')->references('id')->on('levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roleplay_scenarios');
        Schema::dropIfExists('premium_features');
        Schema::dropIfExists('premium_plans');
        Schema::dropIfExists('featured_playlists');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('video_playlists');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('dictionary_entries');
        Schema::dropIfExists('lesson_word');
        Schema::dropIfExists('lesson_dialogues');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('words');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('levels');
    }
};
