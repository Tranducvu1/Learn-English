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
    /** @var array<string, list<string>> */
    private const VI_HINTS = [
        'xin chào' => ['你好', '您好'],
        'chào hỏi' => ['你好', '您好'],
        'chào' => ['你好'],
        'cảm ơn' => ['谢谢', '多谢'],
        'tạm biệt' => ['再见'],
        'xin lỗi' => ['对不起', '不好意思'],
        'không sao' => ['没关系', '不客气'],
        'tên' => ['名字', '叫'],
        'bao nhiêu' => ['多少', '几'],
        'mua' => ['买', '商店'],
        'ăn' => ['吃', '饭'],
        'uống' => ['喝', '水'],
        'học' => ['学习', '学'],
        'làm việc' => ['工作'],
        'phỏng vấn' => ['面试', '工作'],
    ];

    public function retrieve(string $message, ?string $hskLevel = null, int $limit = 8): array
    {
        $context = [];
        $seen = [];

        $this->pushUnique($context, $seen, $this->fromHanzi($message, $hskLevel));
        $this->pushUnique($context, $seen, $this->fromVietnamese($message, $hskLevel));
        $this->pushUnique($context, $seen, $this->fromPinyin($message, $hskLevel));
        $this->pushUnique($context, $seen, $this->fromDialogues($message, $hskLevel));
        $this->pushUnique($context, $seen, $this->fromHintMap($message));

        if (count($context) < $limit && $hskLevel) {
            $this->pushUnique($context, $seen, $this->levelDialogues($hskLevel, $limit - count($context)));
        }

        if (count($context) < $limit && $hskLevel) {
            $this->pushUnique($context, $seen, $this->levelWords($hskLevel, $limit - count($context)));
        }

        return array_slice($context, 0, $limit);
    }

    public function formatForPrompt(array $snippets): string
    {
        if ($snippets === []) {
            return '';
        }

        return "Ngữ cảnh từ kho học liệu HanViet (ưu tiên khi trả lời):\n- "
            .implode("\n- ", $snippets);
    }

    /** @param list<string> $target */
    private function pushUnique(array &$context, array &$seen, array $items): void
    {
        foreach ($items as $item) {
            if ($item === '' || isset($seen[$item])) {
                continue;
            }
            $seen[$item] = true;
            $context[] = $item;
        }
    }

    /** @return list<string> */
    private function fromHanzi(string $message, ?string $hskLevel): array
    {
        $tokens = $this->extractHanziTokens($message);
        if ($tokens === []) {
            return [];
        }

        $context = [];
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
            $context[] = $this->formatWord($w);
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

        return $context;
    }

    /** @return list<string> */
    private function fromVietnamese(string $message, ?string $hskLevel): array
    {
        $keywords = $this->extractVietnameseKeywords($message);
        if ($keywords === []) {
            return [];
        }

        $context = [];
        $wordQuery = Word::query()
            ->when($hskLevel, fn ($q) => $q->where('hsk', '<=', $this->levelNum($hskLevel) + 1));

        $wordQuery->where(function ($q) use ($keywords) {
            foreach ($keywords as $kw) {
                $q->orWhere('vietnamese', 'like', "%{$kw}%")
                    ->orWhere('english', 'like', "%{$kw}%");
            }
        });

        foreach ($wordQuery->limit(5)->get() as $w) {
            $context[] = $this->formatWord($w);
        }

        DictionaryEntry::query()
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('vietnamese', 'like', "%{$kw}%");
                }
            })
            ->limit(3)
            ->get()
            ->each(function ($d) use (&$context) {
                $context[] = "Từ điển [{$d->hanzi}] {$d->pinyin}: {$d->vietnamese}";
            });

        return $context;
    }

    /** @return list<string> */
    private function fromPinyin(string $message, ?string $hskLevel): array
    {
        $normalized = $this->normalizePinyin($message);
        if (mb_strlen($normalized) < 2) {
            return [];
        }

        $context = [];
        Word::query()
            ->when($hskLevel, fn ($q) => $q->where('hsk', $this->levelNum($hskLevel)))
            ->where('pinyin', 'like', "%{$normalized}%")
            ->limit(4)
            ->get()
            ->each(function ($w) use (&$context) {
                $context[] = $this->formatWord($w);
            });

        return $context;
    }

    /** @return list<string> */
    private function fromDialogues(string $message, ?string $hskLevel): array
    {
        $keywords = $this->extractVietnameseKeywords($message);
        $hanzi = $this->extractHanziTokens($message);
        if ($keywords === [] && $hanzi === []) {
            return [];
        }

        $query = LessonDialogue::query()
            ->when($hskLevel, fn ($q) => $q->whereHas('lesson', fn ($lq) => $lq->where('level_id', $hskLevel)));

        $query->where(function ($q) use ($keywords, $hanzi) {
            foreach ($keywords as $kw) {
                $q->orWhere('vietnamese', 'like', "%{$kw}%");
            }
            foreach ($hanzi as $hz) {
                $q->orWhere('hanzi', 'like', "%{$hz}%");
            }
        });

        $context = [];
        foreach ($query->limit(4)->get() as $dlg) {
            $speaker = $dlg->speaker ? "{$dlg->speaker}: " : '';
            $context[] = "Hội thoại {$speaker}{$dlg->hanzi} ({$dlg->pinyin}) — {$dlg->vietnamese}";
        }

        return $context;
    }

    /** @return list<string> */
    private function fromHintMap(string $message): array
    {
        $lower = mb_strtolower(trim($message));
        $hanziTargets = [];

        foreach (self::VI_HINTS as $phrase => $hanzis) {
            if (str_contains($lower, $phrase)) {
                foreach ($hanzis as $hz) {
                    $hanziTargets[] = $hz;
                }
            }
        }

        if ($hanziTargets === []) {
            return [];
        }

        $context = [];
        Word::query()
            ->whereIn('hanzi', array_unique($hanziTargets))
            ->limit(5)
            ->get()
            ->each(function ($w) use (&$context) {
                $context[] = $this->formatWord($w);
            });

        if ($context === []) {
            DictionaryEntry::query()
                ->whereIn('hanzi', array_unique($hanziTargets))
                ->limit(5)
                ->get()
                ->each(function ($d) use (&$context) {
                    $context[] = "Từ điển [{$d->hanzi}] {$d->pinyin}: {$d->vietnamese}";
                });
        }

        return $context;
    }

    /** @return list<string> */
    private function levelDialogues(string $hskLevel, int $limit): array
    {
        return LessonDialogue::query()
            ->whereHas('lesson', fn ($q) => $q->where('level_id', $hskLevel))
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(fn ($dlg) => "Hội thoại mẫu: {$dlg->hanzi} ({$dlg->pinyin}) — {$dlg->vietnamese}")
            ->all();
    }

    /** @return list<string> */
    private function levelWords(string $hskLevel, int $limit): array
    {
        return Word::query()
            ->where('hsk', $this->levelNum($hskLevel))
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn ($w) => $this->formatWord($w))
            ->all();
    }

    private function formatWord(Word $w): string
    {
        $vi = $w->vietnamese ?: $w->english;

        return "Từ [{$w->hanzi}] {$w->pinyin}: {$vi} (HSK{$w->hsk})";
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

    /** @return list<string> */
    private function extractVietnameseKeywords(string $text): array
    {
        $lower = mb_strtolower(trim($text));
        $keywords = [];

        foreach (array_keys(self::VI_HINTS) as $phrase) {
            if (str_contains($lower, $phrase)) {
                $keywords[] = $phrase;
            }
        }

        $stop = ['là', 'gì', 'của', 'và', 'có', 'không', 'tiếng', 'trung', 'nghĩa', 'từ', 'cho', 'mình', 'em', 'anh', 'chị', 'the', 'how', 'what'];
        $parts = preg_split('/[\s,.?!;:]+/u', $lower) ?: [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (mb_strlen($part) >= 3 && ! in_array($part, $stop, true)) {
                $keywords[] = $part;
            }
        }

        return array_values(array_unique($keywords));
    }

    private function normalizePinyin(string $text): string
    {
        $lower = mb_strtolower($text);
        $map = ['á' => 'a', 'à' => 'a', 'ǎ' => 'a', 'ā' => 'a', 'é' => 'e', 'è' => 'e', 'ě' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ǐ' => 'i', 'ī' => 'i', 'ó' => 'o', 'ò' => 'o', 'ǒ' => 'o', 'ō' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ǔ' => 'u', 'ū' => 'u', 'ǘ' => 'v', 'ǜ' => 'v', 'ǚ' => 'v', 'ǖ' => 'v',
            'ü' => 'v', 'ń' => 'n', 'ň' => 'n', 'ǹ' => 'n'];

        return strtr($lower, $map);
    }

    private function levelNum(?string $levelId): int
    {
        if (! $levelId) {
            return 1;
        }

        return (int) Str::afterLast($levelId, 'hsk') ?: 1;
    }
}
