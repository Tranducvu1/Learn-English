<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false);
            $table->unsignedSmallInteger('streak')->default(0);
            $table->date('last_study_date')->nullable();
            $table->unsignedInteger('total_study_minutes')->default(0);
            $table->unsignedInteger('words_learned')->default(0);
        });

        Schema::create('user_settings', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->boolean('dark_mode')->default(false);
            $table->boolean('show_pinyin')->default(true);
            $table->string('font_size', 16)->default('medium');
            $table->string('tts_engine', 16)->default('youdao');
            $table->timestamps();
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('lesson_id');
            $table->string('level_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'lesson_id']);
            $table->foreign('lesson_id')->references('id')->on('lessons')->cascadeOnDelete();
        });

        Schema::create('hsk_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('level_id');
            $table->unsignedTinyInteger('percent')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'level_id']);
            $table->foreign('level_id')->references('id')->on('levels')->cascadeOnDelete();
        });

        Schema::create('srs_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('word_id');
            $table->decimal('ease', 4, 2)->default(2.5);
            $table->unsignedInteger('interval_days')->default(0);
            $table->unsignedSmallInteger('repetitions')->default(0);
            $table->timestamp('next_review_at')->nullable();
            $table->timestamp('last_review_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'word_id']);
            $table->foreign('word_id')->references('id')->on('words')->cascadeOnDelete();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('quiz_id');
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total')->default(0);
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
        });

        Schema::create('study_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('lesson_id')->nullable();
            $table->string('level_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['user_id', 'logged_at']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_slug');
            $table->string('status', 32)->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('payment_provider')->nullable();
            $table->string('payment_ref')->nullable();
            $table->timestamps();

            $table->foreign('plan_slug')->references('slug')->on('premium_plans');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('study_logs');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('srs_cards');
        Schema::dropIfExists('hsk_progress');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('user_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_premium', 'streak', 'last_study_date', 'total_study_minutes', 'words_learned']);
        });
    }
};
