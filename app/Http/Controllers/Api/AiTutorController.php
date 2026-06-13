<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiTutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTutorController extends Controller
{
    public function __construct(private AiTutorService $aiTutor) {}

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'session_id' => ['nullable', 'uuid'],
            'mode' => ['nullable', 'string', 'in:tutor,roleplay'],
            'scenario_id' => ['nullable', 'string'],
            'hsk_level' => ['nullable', 'string'],
        ]);

        $result = $this->aiTutor->chat(
            $request->user(),
            $data['message'],
            $data['session_id'] ?? null,
            $data
        );

        return response()->json($result);
    }

    public function sessions(Request $request): JsonResponse
    {
        $sessions = $request->user()
            ->aiChatSessions()
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'mode' => $s->mode,
                'title' => $s->title,
                'hsk_level' => $s->hsk_level,
                'messages_count' => $s->messages_count,
                'updated_at' => $s->updated_at?->toIso8601String(),
            ]);

        return response()->json(['sessions' => $sessions]);
    }
}
