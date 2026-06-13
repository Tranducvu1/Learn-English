<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HskProgress;
use App\Models\LessonProgress;
use App\Models\SrsCard;
use App\Models\StudyLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('settings');

        $lessonProgress = LessonProgress::where('user_id', $user->id)->get()->keyBy('lesson_id');
        $hskProgress = HskProgress::where('user_id', $user->id)->pluck('percent', 'level_id');
        $srsCards = SrsCard::where('user_id', $user->id)->get()->keyBy('word_id');
        $quizScores = DB::table('quiz_attempts')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->unique('quiz_id')
            ->pluck('score', 'quiz_id');
        $studyLog = StudyLog::where('user_id', $user->id)
            ->orderByDesc('logged_at')
            ->limit(40)
            ->get();

        return response()->json([
            'isPremium' => $user->hasPremiumAccess(),
            'streak' => $user->streak,
            'lastStudyDate' => $user->last_study_date?->format('Y-m-d'),
            'totalStudyMinutes' => $user->total_study_minutes,
            'wordsLearned' => $user->words_learned,
            'completedLessons' => $lessonProgress->where('completed', true)->keys()->values(),
            'lessonProgress' => $lessonProgress->mapWithKeys(fn ($p) => [
                $p->lesson_id => [
                    'levelId' => $p->level_id,
                    'startedAt' => $p->started_at?->timestamp,
                    'lastOpenedAt' => $p->last_opened_at?->timestamp,
                    'completed' => $p->completed,
                    'completedAt' => $p->completed_at?->timestamp,
                ],
            ]),
            'hskProgress' => $hskProgress,
            'srsCards' => $srsCards->mapWithKeys(fn ($c) => [
                $c->word_id => [
                    'wordId' => $c->word_id,
                    'ease' => (float) $c->ease,
                    'interval' => $c->interval_days,
                    'repetitions' => $c->repetitions,
                    'nextReview' => $c->next_review_at?->timestamp,
                    'lastReview' => $c->last_review_at?->timestamp,
                ],
            ]),
            'quizScores' => $quizScores,
            'settings' => $user->settings ? [
                'darkMode' => $user->settings->dark_mode,
                'showPinyin' => $user->settings->show_pinyin,
                'fontSize' => $user->settings->font_size,
            ] : null,
            'studyLog' => $studyLog->map(fn ($l) => [
                'type' => $l->type,
                'lessonId' => $l->lesson_id,
                'levelId' => $l->level_id,
                'title' => $l->title,
                'at' => $l->logged_at->timestamp,
            ]),
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'streak' => ['nullable', 'integer', 'min:0'],
            'lastStudyDate' => ['nullable', 'date'],
            'totalStudyMinutes' => ['nullable', 'integer', 'min:0'],
            'wordsLearned' => ['nullable', 'integer', 'min:0'],
            'completedLessons' => ['nullable', 'array'],
            'lessonProgress' => ['nullable', 'array'],
            'hskProgress' => ['nullable', 'array'],
            'srsCards' => ['nullable', 'array'],
            'quizScores' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'studyLog' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $data) {
            $user->update(array_filter([
                'streak' => $data['streak'] ?? null,
                'last_study_date' => $data['lastStudyDate'] ?? null,
                'total_study_minutes' => $data['totalStudyMinutes'] ?? null,
                'words_learned' => $data['wordsLearned'] ?? null,
            ], fn ($v) => $v !== null));

            if (! empty($data['settings'])) {
                $user->settings()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'dark_mode' => $data['settings']['darkMode'] ?? false,
                        'show_pinyin' => $data['settings']['showPinyin'] ?? true,
                        'font_size' => $data['settings']['fontSize'] ?? 'medium',
                    ]
                );
            }

            foreach ($data['lessonProgress'] ?? [] as $lessonId => $progress) {
                LessonProgress::updateOrCreate(
                    ['user_id' => $user->id, 'lesson_id' => $lessonId],
                    [
                        'level_id' => $progress['levelId'] ?? null,
                        'started_at' => isset($progress['startedAt']) ? date('Y-m-d H:i:s', $progress['startedAt']) : null,
                        'last_opened_at' => isset($progress['lastOpenedAt']) ? date('Y-m-d H:i:s', $progress['lastOpenedAt']) : now(),
                        'completed' => $progress['completed'] ?? false,
                        'completed_at' => isset($progress['completedAt']) ? date('Y-m-d H:i:s', $progress['completedAt']) : null,
                    ]
                );
            }

            foreach ($data['hskProgress'] ?? [] as $levelId => $percent) {
                HskProgress::updateOrCreate(
                    ['user_id' => $user->id, 'level_id' => $levelId],
                    ['percent' => (int) $percent]
                );
            }

            foreach ($data['srsCards'] ?? [] as $wordId => $card) {
                SrsCard::updateOrCreate(
                    ['user_id' => $user->id, 'word_id' => $wordId],
                    [
                        'ease' => $card['ease'] ?? 2.5,
                        'interval_days' => $card['interval'] ?? 0,
                        'repetitions' => $card['repetitions'] ?? 0,
                        'next_review_at' => isset($card['nextReview']) ? date('Y-m-d H:i:s', $card['nextReview']) : null,
                        'last_review_at' => isset($card['lastReview']) ? date('Y-m-d H:i:s', $card['lastReview']) : null,
                    ]
                );
            }

            foreach ($data['studyLog'] ?? [] as $entry) {
                StudyLog::create([
                    'user_id' => $user->id,
                    'type' => $entry['type'] ?? 'study',
                    'lesson_id' => $entry['lessonId'] ?? null,
                    'level_id' => $entry['levelId'] ?? null,
                    'title' => $entry['title'] ?? null,
                    'logged_at' => isset($entry['at']) ? date('Y-m-d H:i:s', $entry['at']) : now(),
                ]);
            }
        });

        $request->user()->refresh()->load('settings');

        return $this->show($request);
    }

    public function completeLesson(Request $request, string $lessonId): JsonResponse
    {
        $user = $request->user();

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lessonId],
            [
                'completed' => true,
                'completed_at' => now(),
                'last_opened_at' => now(),
            ]
        );

        StudyLog::create([
            'user_id' => $user->id,
            'type' => 'lesson_complete',
            'lesson_id' => $lessonId,
            'title' => $request->input('title'),
            'logged_at' => now(),
        ]);

        return response()->json(['ok' => true, 'lesson_id' => $lessonId]);
    }
}
