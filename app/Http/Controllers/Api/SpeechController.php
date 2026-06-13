<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PronunciationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpeechController extends Controller
{
    public function __construct(private PronunciationService $pronunciation) {}

    public function transcribe(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'mimes:webm,wav,mp3,m4a,ogg', 'max:10240'],
            'language' => ['nullable', 'string', 'max:8'],
        ]);

        $result = $this->pronunciation->transcribe(
            $request->user(),
            $request->file('audio'),
            $request->input('language', 'zh')
        );

        return response()->json($result);
    }

    public function score(Request $request): JsonResponse
    {
        $data = $request->validate([
            'target_text' => ['required', 'string', 'max:500'],
            'transcript' => ['nullable', 'string', 'max:500'],
            'audio' => ['nullable', 'file', 'mimes:webm,wav,mp3,m4a,ogg', 'max:10240'],
            'word_id' => ['nullable', 'string'],
        ]);

        $result = $this->pronunciation->score(
            $request->user(),
            $data['target_text'],
            $data['transcript'] ?? null,
            $request->file('audio')
        );

        return response()->json($result);
    }
}
