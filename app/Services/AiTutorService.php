<?php

namespace App\Services;

use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiTutorService
{
    public function __construct(
        private LessonContextRetriever $contextRetriever,
    ) {}

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

        $hskLevel = $session->hsk_level ?? $options['hsk_level'] ?? 'hsk1';
        $ragSnippets = $this->contextRetriever->retrieve($message, $hskLevel);

        $reply = $this->generateReply($user, $session, $message, $options, $ragSnippets);

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

    private function generateReply(User $user, AiChatSession $session, string $message, array $options, array $ragSnippets): array
    {
        $apiKey = config('services.openai.key');
        $openaiConfigured = $apiKey !== null && $apiKey !== '';

        if (! $openaiConfigured) {
            return $this->ragAwareFallback($message, $options, $ragSnippets);
        }

        $history = $session->messages()
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->map(fn (AiChatMessage $m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        $systemPrompt = $this->buildSystemPrompt($session, $options, $ragSnippets);

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
            return $this->ragAwareFallback($message, $options, $ragSnippets, 'openai_error');
        }

        $content = $response->json('choices.0.message.content', '');

        return [
            'content' => $content,
            'metadata' => $this->buildMetadata($ragSnippets, 'openai', true),
        ];
    }

    private function buildSystemPrompt(AiChatSession $session, array $options, array $ragSnippets = []): string
    {
        $level = $session->hsk_level ?? 'hsk1';
        $mode = $session->mode ?? 'tutor';

        $base = "Bạn là gia sư tiếng Trung cho người Việt. Trình độ học viên: {$level}. "
            .'Trả lời bằng tiếng Trung đơn giản phù hợp level, kèm pinyin và giải thích tiếng Việt ngắn gọn. '
            .'Sửa lỗi ngữ pháp/phát âm nếu học viên viết sai. '
            .'BẮT BUỘC ưu tiên dùng từ vựng và hội thoại từ ngữ cảnh RAG bên dưới — trích dẫn hanzi cụ thể từ kho HanViet.';

        $rag = $this->contextRetriever->formatForPrompt($ragSnippets);
        if ($rag !== '') {
            $base .= "\n\n{$rag}";
        }

        if ($mode === 'roleplay' && $session->scenario_id) {
            $base .= " Chế độ role-play: {$session->scenario_id}. Hãy đóng vai tình huống thực tế.";
        }

        return $base;
    }

    private function ragAwareFallback(string $message, array $options, array $ragSnippets, string $reason = 'no_openai'): array
    {
        if ($ragSnippets !== []) {
            $lines = array_slice($ragSnippets, 0, 5);
            $body = "📚 Kho HanViet (RAG) — tìm thấy ".count($ragSnippets)." mẩu liên quan:\n\n";
            foreach ($lines as $line) {
                $body .= "• {$line}\n";
            }
            $body .= "\nBạn hỏi: {$message}\n\n";
            $body .= '💡 Thử đặt câu với từ trên. Để AI giải thích sâu hơn, admin cần cấu hình OPENAI_API_KEY trên server.';

            return [
                'content' => $body,
                'metadata' => $this->buildMetadata($ragSnippets, 'rag_fallback', false, $reason),
            ];
        }

        $responses = [
            "你好！Bạn hỏi: 「{$message}」\n\nChưa tìm thấy từ khóa trong kho 1.200 từ. Thử: 「你好」「谢谢」hoặc hỏi tiếng Việt \"xin chào tiếng Trung là gì\".",
            "Hãy thử câu có Hán tự hoặc từ tiếng Việt (vd: chào, cảm ơn, mua). RAG sẽ tra từ điển + hội thoại bài học.\n\nBạn: {$message}",
        ];

        return [
            'content' => $responses[array_rand($responses)],
            'metadata' => $this->buildMetadata([], 'fallback', false, $reason),
        ];
    }

    /** @return array<string, mixed> */
    private function buildMetadata(array $ragSnippets, string $provider, bool $openaiConfigured, ?string $reason = null): array
    {
        return [
            'provider' => $provider,
            'openai_configured' => $openaiConfigured,
            'rag' => count($ragSnippets) > 0,
            'rag_count' => count($ragSnippets),
            'rag_snippets' => array_slice($ragSnippets, 0, 5),
            'fallback_reason' => $reason,
        ];
    }
}
