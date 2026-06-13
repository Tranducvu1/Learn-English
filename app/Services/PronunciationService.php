<?php

namespace App\Services;

use App\Models\PronunciationAttempt;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PronunciationService
{
    public function transcribe(User $user, UploadedFile $audio, ?string $language = 'zh'): array
    {
        $path = $audio->store('speech/'.$user->id, 'local');
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            return [
                'transcript' => '你好',
                'demo' => true,
                'audio_path' => $path,
            ];
        }

        $response = Http::withToken($apiKey)
            ->attach('file', file_get_contents($audio->getRealPath()), $audio->getClientOriginalName())
            ->post(config('services.openai.base_url').'/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => $language,
            ]);

        if (! $response->successful()) {
            return [
                'transcript' => '',
                'error' => 'transcription_failed',
                'audio_path' => $path,
            ];
        }

        return [
            'transcript' => $response->json('text', ''),
            'audio_path' => $path,
        ];
    }

    public function score(User $user, string $targetText, ?string $transcript = null, ?UploadedFile $audio = null): array
    {
        if ($audio && ! $transcript) {
            $transcribed = $this->transcribe($user, $audio);
            $transcript = $transcribed['transcript'] ?? '';
        }

        $transcript = $transcript ?? '';
        $score = $this->calculateScore($targetText, $transcript);
        $feedback = $this->buildFeedback($targetText, $transcript, $score);

        $attempt = PronunciationAttempt::create([
            'user_id' => $user->id,
            'target_text' => $targetText,
            'transcript' => $transcript,
            'score' => $score,
            'feedback' => $feedback,
            'audio_path' => isset($transcribed) ? ($transcribed['audio_path'] ?? null) : null,
        ]);

        return [
            'attempt_id' => $attempt->id,
            'target' => $targetText,
            'transcript' => $transcript,
            'score' => $score,
            'feedback' => $feedback,
        ];
    }

    private function calculateScore(string $target, string $spoken): int
    {
        if ($spoken === '') {
            return 0;
        }

        $targetChars = $this->normalizeChinese($target);
        $spokenChars = $this->normalizeChinese($spoken);

        if ($targetChars === $spokenChars) {
            return 100;
        }

        similar_text($targetChars, $spokenChars, $percent);

        return (int) round(min(99, max(10, $percent)));
    }

    private function normalizeChinese(string $text): string
    {
        return preg_replace('/[\s\p{P}]/u', '', $text) ?? $text;
    }

    private function buildFeedback(string $target, string $spoken, int $score): array
    {
        $tips = [];

        if ($score >= 90) {
            $tips[] = 'Phát âm rất tốt! Giữ nhịp và thanh điệu ổn định.';
        } elseif ($score >= 70) {
            $tips[] = 'Gần đúng — chú ý thanh điệu (shengmu/yunmu).';
        } else {
            $tips[] = 'Nghe lại audio chuẩn và shadowing từng âm tiết.';
        }

        if ($spoken && $target !== $spoken) {
            $tips[] = "Bạn nói: 「{$spoken}」 — mục tiêu: 「{$target}」";
        }

        return [
            'score_label' => $score >= 85 ? 'excellent' : ($score >= 60 ? 'good' : 'needs_practice'),
            'tips' => $tips,
            'waveform_demo' => true,
        ];
    }
}
