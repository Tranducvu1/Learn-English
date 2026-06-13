<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;

class LevelController extends Controller
{
    public function index(): JsonResponse
    {
        $levels = Level::withCount('lessons')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Level $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'color' => $l->color,
                'description' => $l->description,
                'totalLessons' => $l->lessons_count,
            ]);

        return response()->json(['levels' => $levels]);
    }

    public function show(string $id): JsonResponse
    {
        $level = Level::with(['lessons' => fn ($q) => $q->with('topic')->orderBy('id')])
            ->findOrFail($id);

        return response()->json([
            'level' => [
                'id' => $level->id,
                'name' => $level->name,
                'color' => $level->color,
                'description' => $level->description,
                'lessons' => $level->lessons->map(fn ($lesson) => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'topic' => $lesson->topic_id,
                    'duration' => $lesson->duration,
                    'skills' => $lesson->skills,
                ]),
            ],
        ]);
    }
}
