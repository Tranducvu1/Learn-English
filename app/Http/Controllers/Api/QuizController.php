<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Quiz::withCount('questions');

        if ($request->filled('level')) {
            $query->where('level_id', $request->string('level'));
        }

        $quizzes = $query->orderBy('id')->get();

        return response()->json([
            'quizzes' => $quizzes->map(fn (Quiz $q) => [
                'id' => $q->id,
                'title' => $q->title,
                'level' => $q->level_id,
                'lessonId' => $q->lesson_id,
                'questionCount' => $q->questions_count,
            ]),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $includeAnswers = $request->boolean('answers', false);

        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'level' => $quiz->level_id,
                'lessonId' => $quiz->lesson_id,
                'questions' => $quiz->questions->map(function ($q) use ($includeAnswers) {
                    $item = [
                        'id' => $q->external_id ?? (string) $q->id,
                        'type' => $q->type,
                        'question' => $q->question,
                        'hanzi' => $q->hanzi,
                        'audioText' => $q->audio_text,
                        'options' => $q->options,
                        'explanation' => $q->explanation,
                    ];
                    if ($includeAnswers) {
                        $item['correct'] = $q->correct_index;
                    }

                    return $item;
                }),
            ],
        ]);
    }
}
