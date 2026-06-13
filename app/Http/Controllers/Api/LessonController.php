<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Lesson::with(['level', 'topic']);

        if ($request->filled('level')) {
            $query->where('level_id', $request->string('level'));
        }
        if ($request->filled('topic')) {
            $query->where('topic_id', $request->string('topic'));
        }

        $lessons = $query->orderBy('id')->get();

        return response()->json([
            'lessons' => $lessons->map(fn (Lesson $l) => $this->lessonSummary($l)),
        ]);
    }

    public function meta(): JsonResponse
    {
        $levels = Level::with(['lessons' => fn ($q) => $q->orderBy('id')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Level $level) => [
                'id' => $level->id,
                'name' => $level->name,
                'color' => $level->color,
                'description' => $level->description,
                'totalLessons' => $level->lessons->count(),
                'lessons' => $level->lessons->map(fn (Lesson $l) => $this->lessonSummary($l)),
            ]);

        $topics = Topic::orderBy('name')->get()->map(fn (Topic $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'icon' => $t->icon,
            'lessonCount' => $t->lesson_count,
        ]);

        return response()->json([
            'levels' => $levels,
            'topics' => $topics,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $lesson = Lesson::with(['level', 'topic', 'dialogues', 'words'])
            ->findOrFail($id);

        return response()->json([
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'topic' => $lesson->topic_id,
                'duration' => $lesson->duration,
                'vocabIds' => $lesson->words->pluck('hanzi')->values(),
                'skills' => $lesson->skills ?? [],
                'content' => [
                    'intro' => $lesson->intro,
                    'dialogue' => $lesson->dialogues->map(fn ($d) => [
                        'speaker' => $d->speaker,
                        'hanzi' => $d->hanzi,
                        'pinyin' => $d->pinyin,
                        'vietnamese' => $d->vietnamese,
                    ]),
                ],
                'level' => $lesson->level ? [
                    'id' => $lesson->level->id,
                    'name' => $lesson->level->name,
                    'color' => $lesson->level->color,
                ] : null,
                'vocabulary' => $lesson->words->map(fn ($w) => [
                    'id' => $w->id,
                    'hanzi' => $w->hanzi,
                    'pinyin' => $w->pinyin,
                    'vietnamese' => $w->vietnamese,
                    'english' => $w->english,
                    'hsk' => $w->hsk,
                    'example' => $w->example,
                ]),
            ],
        ]);
    }

    private function lessonSummary(Lesson $lesson): array
    {
        return [
            'id' => $lesson->id,
            'title' => $lesson->title,
            'topic' => $lesson->topic_id,
            'duration' => $lesson->duration,
            'skills' => $lesson->skills ?? [],
        ];
    }
}
