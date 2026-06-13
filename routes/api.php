<?php

use App\Http\Controllers\Api\AiTutorController;
use App\Http\Controllers\Api\AppDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DictionaryController;
use App\Http\Controllers\Api\EventStreamController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SpeechController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\WordController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'ok' => true,
    'service' => 'hanviet-api',
    'version' => '1.0.0',
]));

Route::prefix('v1')->group(function () {
    // Bootstrap — thay window.APP_DATA / data-bundle.js
    Route::get('/bootstrap', [AppDataController::class, 'bootstrap']);

    // Content (granular — admin / future React)
    Route::get('/levels', [LevelController::class, 'index']);
    Route::get('/levels/{id}', [LevelController::class, 'show']);
    Route::get('/topics', [TopicController::class, 'index']);
    Route::get('/lessons/meta', [LessonController::class, 'meta']);
    Route::get('/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{id}', [LessonController::class, 'show']);
    Route::get('/words', [WordController::class, 'index']);
    Route::get('/words/{id}', [WordController::class, 'show']);
    Route::get('/dictionary', [DictionaryController::class, 'index']);
    Route::get('/quizzes', [QuizController::class, 'index']);
    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/premium', [PremiumController::class, 'index']);

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Public SSE (review-code integration)
    Route::get('/events/stream', [EventStreamController::class, 'stream']);
    Route::get('/events/history', [EventStreamController::class, 'history']);
    Route::post('/events/review', [EventStreamController::class, 'triggerReview']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/me/progress', [ProgressController::class, 'show']);
        Route::post('/me/progress/sync', [ProgressController::class, 'sync']);
        Route::post('/me/lessons/{lessonId}/complete', [ProgressController::class, 'completeLesson']);

        Route::post('/premium/demo', [PremiumController::class, 'demoActivate']);

        Route::middleware('premium')->group(function () {
            Route::post('/ai/tutor/chat', [AiTutorController::class, 'chat']);
            Route::get('/ai/tutor/sessions', [AiTutorController::class, 'sessions']);
            Route::post('/speech/transcribe', [SpeechController::class, 'transcribe']);
            Route::post('/speech/score', [SpeechController::class, 'score']);
        });
    });
});
