<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function hocTiengTrung(): View
    {
        return $this->page([
            'slug' => 'hoc-tieng-trung',
            'title' => 'Học tiếng Trung online miễn phí — 汉越学堂',
            'description' => 'Học tiếng Trung online miễn phí với 214 bài học HSK, 1.200 từ vựng, flashcard SRS và luyện thi thử. Thiết kế cho người Việt.',
            'h1' => 'Học tiếng Trung online — miễn phí & có lộ trình',
            'intro' => '汉越学堂 giúp bạn học tiếng Trung từ zero với hội thoại thực tế, pinyin, nghĩa tiếng Việt và luyện thi HSK 1–6.',
            'ctaPage' => 'lessons',
            'ctaLabel' => 'Bắt đầu học ngay',
            'features' => [
                ['icon' => '📖', 'title' => '214 bài học HSK', 'desc' => 'Hội thoại + từ vựng theo cấp độ'],
                ['icon' => '🃏', 'title' => 'Flashcard SRS', 'desc' => 'Ôn từ thông minh, nhớ lâu'],
                ['icon' => '📝', 'title' => 'Luyện thi HSK', 'desc' => 'Đề thi thử mô phỏng format thật'],
            ],
        ]);
    }

    public function luyenThiHsk(): View
    {
        return $this->page([
            'slug' => 'luyen-thi-hsk',
            'title' => 'Luyện thi HSK online — Đề thi thử HSK 1–6',
            'description' => 'Luyện thi HSK online với đề thi thử, giải thích chi tiết và mẹo đạt điểm cao. Miễn phí trên 汉越学堂.',
            'h1' => 'Luyện thi HSK 1–6 online',
            'intro' => 'Làm bài kiểm tra theo format đề thi thật, xem giải thích và theo dõi tiến độ từng cấp HSK.',
            'ctaPage' => 'quiz',
            'ctaLabel' => 'Làm đề thi thử',
            'features' => [
                ['icon' => '📝', 'title' => '86 bộ đề quiz', 'desc' => 'HSK 1 đến HSK 6'],
                ['icon' => '💡', 'title' => 'Mẹo thi', 'desc' => 'Chiến lược theo từng cấp'],
                ['icon' => '📊', 'title' => 'Theo dõi điểm', 'desc' => 'Lưu kết quả khi đăng ký'],
            ],
        ]);
    }

    public function tuVungHsk(): View
    {
        return $this->page([
            'slug' => 'tu-vung-hsk',
            'title' => 'Từ vựng HSK — 1.200 từ có pinyin & tiếng Việt',
            'description' => 'Kho từ vựng HSK 1–6: 1.200 từ với pinyin, nghĩa tiếng Việt, flashcard SRS và tra cứu nhanh.',
            'h1' => 'Từ vựng HSK — 1.200 từ',
            'intro' => 'Học và ôn từ vựng HSK theo chủ đề, cấp độ và chế độ spaced repetition.',
            'ctaPage' => 'vocabulary',
            'ctaLabel' => 'Mở kho từ vựng',
            'features' => [
                ['icon' => '📚', 'title' => '1.200 từ HSK', 'desc' => 'Hán tự, pinyin, tiếng Việt'],
                ['icon' => '🔊', 'title' => 'Nghe phát âm', 'desc' => 'Youdao & giọng trình duyệt'],
                ['icon' => '🃏', 'title' => 'Ôn flashcard', 'desc' => 'SRS tự động sắp lịch'],
            ],
        ]);
    }

    public function hskLevel(int $level): View
    {
        abort_unless($level >= 1 && $level <= 6, 404);

        $meta = config("hanviet.seo.hsk_levels.{$level}", []);

        return $this->page([
            'slug' => "hsk-{$level}",
            'title' => $meta['title'] ?? "Học HSK {$level} online miễn phí",
            'description' => $meta['description'] ?? "Học tiếng Trung HSK {$level} online trên 汉越学堂.",
            'h1' => $meta['h1'] ?? "Học tiếng Trung HSK {$level}",
            'intro' => $meta['intro'] ?? '',
            'ctaPage' => 'lessons',
            'ctaHsk' => $level,
            'ctaLabel' => "Học HSK {$level} ngay",
            'features' => $meta['features'] ?? [],
        ]);
    }

    private function page(array $data): View
    {
        $query = [];
        if (! empty($data['ctaPage'])) {
            $query['page'] = $data['ctaPage'];
        }
        if (! empty($data['ctaHsk'])) {
            $query['hsk'] = $data['ctaHsk'];
        }

        $data['appName'] = config('hanviet.name');
        $data['canonical'] = url('/'.$data['slug']);
        $data['ogImage'] = config('hanviet.seo.og_image_url') ?: url('/og/share.svg');
        $data['ctaUrl'] = url('/').($query ? '?'.http_build_query($query) : '');
        $data['jsonLd'] = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $data['h1'],
            'description' => $data['description'],
            'url' => $data['canonical'],
            'inLanguage' => 'vi',
            'isAccessibleForFree' => true,
            'provider' => [
                '@type' => 'Organization',
                'name' => config('hanviet.name'),
                'url' => url('/'),
            ],
        ];

        return view('landings.page', $data);
    }
}
