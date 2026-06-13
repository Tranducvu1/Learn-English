<?php

namespace App\Console\Commands;

use App\Models\DictionaryEntry;
use App\Models\Word;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EnrichVietnameseCommand extends Command
{
    protected $signature = 'app:enrich-vietnamese {--dry-run : Preview without saving}';

    protected $description = 'Gộp nghĩa tiếng Việt từ dictionary SQL vào bảng words';

    public function handle(): int
    {
        $dict = DictionaryEntry::all()->keyBy('hanzi');
        $updated = 0;
        $skipped = 0;

        Word::orderBy('id')->chunk(200, function ($words) use ($dict, &$updated, &$skipped) {
            foreach ($words as $word) {
                $entry = $dict->get($word->hanzi);
                if (! $entry) {
                    $skipped++;

                    continue;
                }

                $vi = $this->pickVietnamese($entry->vietnamese, $word->vietnamese, $word->english);
                if (! $vi || $vi === $word->vietnamese) {
                    $skipped++;

                    continue;
                }

                if (! $this->option('dry-run')) {
                    $word->update([
                        'vietnamese' => $vi,
                        'english' => $word->english ?: $entry->vietnamese,
                    ]);
                }
                $updated++;
            }
        });

        if (! $this->option('dry-run')) {
            Cache::forget('hanviet_bootstrap_v1');
            Cache::forget('hanviet_bootstrap_v2');
            Cache::forget('hanviet_bootstrap_v3');
        }

        $this->info("Updated {$updated} words, skipped {$skipped}.");

        return self::SUCCESS;
    }

    private function pickVietnamese(?string $dictVi, ?string $currentVi, ?string $currentEn): ?string
    {
        if ($dictVi && $this->looksVietnamese($dictVi)) {
            return trim($dictVi);
        }

        if ($currentVi && $this->looksVietnamese($currentVi)) {
            return trim($currentVi);
        }

        return null;
    }

    private function looksVietnamese(string $text): bool
    {
        if (preg_match('/[àáảãạăằắẳẵặâầấẩẫậèéẻẽẹêềếểễệìíỉĩịòóỏõọôồốổỗộơờớởỡợùúủũụưừứửữựỳýỷỹỵđ]/ui', $text)) {
            return true;
        }

        $lower = mb_strtolower($text);
        $hints = ['không', 'của', 'một', 'người', 'được', 'trong', 'này', 'đó', 'bạn', 'tôi', 'rất', 'có', 'là', 'và', 'để', 'cho', 'khi', 'như', 'cũng', 'đã', 'sẽ', 'xin', 'cảm ơn', 'xin chào'];

        foreach ($hints as $hint) {
            if (str_contains($lower, $hint)) {
                return true;
            }
        }

        return false;
    }
}
