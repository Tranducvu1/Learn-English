<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;

class TopicController extends Controller
{
    public function index(): JsonResponse
    {
        $topics = Topic::orderBy('name')->get()->map(fn (Topic $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'icon' => $t->icon,
            'lessonCount' => $t->lesson_count,
        ]);

        return response()->json(['topics' => $topics]);
    }
}
