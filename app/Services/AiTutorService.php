<?php

namespace App\Services;

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiTutorService
{
    public function chat(User $user, string $message, ?string $sessionId = null, array $options = []): array
    {
        $session = $sessionId
            ? AiChatSession::where('user_id', $user->id)->findOrFail($sessionId)
            : AiChatSession::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'mode' => $options['mode'] ?? 'tutor',
                'scenario_id' => $options['scenario_id'] ?? null,
                'hsk_level' => $options['hsk_level'] ?? 'hsk1',
                'title' => $options['title'] ?? 'AI Tutor',
            ]);

        AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $message,
        ]);

        $reply = $this->generateReply($user, $session, $message, $options);

        AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $reply['content'],
            'metadata' => $reply['metadata'] ?? null,
        ]);

        return [
            'session_id' => $session->id,
            'reply' => $reply['content'],
            'corrections' => $reply['corrections'] ?? [],
            'metadata' => $reply['metadata'] ?? [],
        ];
    }

    private function generateReply(User $user, AiChatSession $session, string $message, array $options): array
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            return $this->fallbackReply($message, $options);
        }

        $history = $session->messages()
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->map(fn (AiChatMessage $m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        $systemPrompt = $this->buildSystemPrompt($session, $options);

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post(config('services.openai.base_url').'/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => array_merge(
                    [['role' => 'system', 'content' => $systemPrompt]],
                    $history
                ),
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

        if (! $response->successful()) {
            return $this->fallbackReply($message, $options);
        }

        $content = $response->json('choices.0.message.content', '');

        return [
            'content' => $content,
            'metadata' => ['provider' => 'openai', 'model' => config('services.openai.model')],
        ];
    }

    private function buildSystemPrompt(AiChatSession $session, array $options): string
    {
        $level = $session->hsk_level ?? 'hsk1';
        $mode = $session->mode ?? 'tutor';

        $base = "Bạn là gia sư tiếng Trung cho người Việt. Trình độ học viên: {$level}. "
            .'Trả lời bằng tiếng Trung đơn giản phù hợp level, kèm pinyin và giải thích tiếng Việt ngắn gọn. '
            .'Sửa lỗi ngữ pháp/phát âm nếu học viên viết sai.';

        if ($mode === 'roleplay' && $session->scenario_id) {
            $base .= " Chế độ role-play: {$session->scenario_id}. Hãy đóng vai tình huống thực tế.";
        }

        return $base;
    }

    private function fallbackReply(string $message, array $options): array
    {
        $responses = [
            "很好！你说：「{$message}」— 继续练习吧！(Rất tốt! Hãy tiếp tục luyện tập nhé!)",
            '你的中文进步很快！试着用完整的句子回答。(Tiến bộ nhanh! Hãy trả lời bằng câu hoàn chỉnh.)',
            '注意声调哦 — 多听听标准发音再模仿。(Chú ý thanh điệu — nghe phát âm chuẩn rồi bắt chước.)',
        ];

        return [
            'content' => $responses[array_rand($responses)],
            'metadata' => ['provider' => 'fallback', 'demo' => true],
        ];
    }
}
