<?php

namespace App\Services;

use App\Models\DictionaryEntry;
use App\Models\LessonDialogue;
use App\Models\Word;
use Illuminate\Support\Str;

/**
 * RAG nhẹ: truy xuất ngữ cảnh từ SQL (từ vựng, từ điển, hội thoại) cho AI Tutor.
 */
class LessonContextRetriever
{
    public function retrieve(string $message, ?string $hskLevel = null, int $limit = 8): array
    {
        $tokens = $this->extractHanziTokens($message);
        $context = [];

        if ($tokens !== []) {
            $words = Word::query()
                ->when($hskLevel, fn ($q) => $q->where('hsk', $this->levelNum($hskLevel)))
                ->where(function ($q) use ($tokens) {
                    foreach ($tokens as $hanzi) {
                        $q->orWhere('hanzi', 'like', "%{$hanzi}%");
                    }
                })
                ->limit(5)
                ->get();

            foreach ($words as $w) {
                $context[] = "Từ [{$w->hanzi}] {$w->pinyin}: {$w->vietnamese} (HSK{$w->hsk})";
            }

            $dict = DictionaryEntry::query()
                ->where(function ($q) use ($tokens) {
                    foreach ($tokens as $hanzi) {
                        $q->orWhere('hanzi', 'like', "%{$hanzi}%");
                    }
                })
                ->limit(3)
                ->get();

            foreach ($dict as $d) {
                $context[] = "Từ điển [{$d->hanzi}] {$d->pinyin}: {$d->vietnamese}";
            }
        }

        if (count($context) < $limit && $hskLevel) {
            $dialogues = LessonDialogue::query()
                ->whereHas('lesson', fn ($q) => $q->where('level_id', $hskLevel))
                ->limit(3)
                ->get();

            foreach ($dialogues as $dlg) {
                $context[] = "Hội thoại: {$dlg->hanzi} ({$dlg->pinyin}) — {$dlg->vietnamese}";
            }
        }

        return array_slice(array_unique($context), 0, $limit);
    }

    public function formatForPrompt(array $snippets): string
    {
        if ($snippets === []) {
            return '';
        }

        return "Ngữ cảnh từ kho học liệu HanViet (ưu tiên khi trả lời):\n- "
            .implode("\n- ", $snippets);
    }

    /** @return list<string> */
    private function extractHanziTokens(string $text): array
    {
        preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $text, $matches);

        $tokens = [];
        foreach ($matches[0] ?? [] as $chunk) {
            if (mb_strlen($chunk) <= 4) {
                $tokens[] = $chunk;
            } else {
                for ($i = 0; $i < mb_strlen($chunk); $i++) {
                    $tokens[] = mb_substr($chunk, $i, 1);
                }
            }
        }

        return array_values(array_unique(array_filter($tokens, fn ($t) => mb_strlen($t) >= 1)));
    }

    private function levelNum(?string $levelId): int
    {
        if (! $levelId) {
            return 1;
        }

        return (int) Str::afterLast($levelId, 'hsk') ?: 1;
    }
}
