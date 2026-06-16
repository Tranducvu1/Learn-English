<?php

return [
    'name' => env('HANVIET_APP_NAME', '汉越学堂'),
    'description' => 'Nền tảng luyện thi HSK & học tiếng Trung online miễn phí',
    'asset_version' => env('HANVIET_ASSET_VERSION', '20260616v17'),
    'data_path' => database_path('data'),

    /*
    | SEO — Search Console, sitemap, landing pages (docs/SEO-SETUP.md)
    */
    'seo' => [
        'site_verification' => trim((string) env('GOOGLE_SITE_VERIFICATION', 'HnAhAwkAB-8eUS_4crVpFemGbP3-Yf6lrTqLZ3968b4')),
        'ga_measurement_id' => trim((string) env('GOOGLE_ANALYTICS_ID', '')),
        'og_image_url' => env('HANVIET_OG_IMAGE_URL') ?: null, // set after deploy or use /og/share.svg
        'hsk_levels' => [
            1 => [
                'title' => 'Học HSK 1 online miễn phí — Bài học & từ vựng cơ bản',
                'description' => 'Học tiếng Trung HSK 1 online: hội thoại cơ bản, từ vựng, flashcard và luyện thi. Miễn phí trên 汉越学堂.',
                'h1' => 'Học tiếng Trung HSK 1 — từ zero',
                'intro' => 'HSK 1 là bước đầu: chào hỏi, số đếm, giao tiếp hàng ngày. Học theo bài có hội thoại và từ vựng tiếng Việt.',
                'features' => [
                    ['icon' => '👋', 'title' => 'Chào hỏi cơ bản', 'desc' => '你好, 谢谢, 再见…'],
                    ['icon' => '📖', 'title' => 'Bài học HSK 1', 'desc' => 'Hội thoại + từ vựng'],
                    ['icon' => '📝', 'title' => 'Đề thi thử', 'desc' => 'Luyện format HSK 1'],
                ],
            ],
            2 => [
                'title' => 'Học HSK 2 online — Luyện thi & từ vựng',
                'description' => 'Học HSK 2 online với bài học, quiz và flashcard. Nền tảng 汉越学堂 miễn phí.',
                'h1' => 'Học tiếng Trung HSK 2',
                'intro' => 'Mở rộng giao tiếp hàng ngày, mua sắm, đi lại — chuẩn bị thi HSK 2.',
                'features' => [
                    ['icon' => '🛒', 'title' => 'Giao tiếp thực tế', 'desc' => 'Chủ đề đời sống'],
                    ['icon' => '🃏', 'title' => 'Flashcard SRS', 'desc' => 'Nhớ từ lâu hơn'],
                    ['icon' => '💡', 'title' => 'Mẹo thi HSK 2', 'desc' => 'Chiến lược điểm cao'],
                ],
            ],
            3 => [
                'title' => 'Học HSK 3 online — Trung cấp sơ',
                'description' => 'Luyện HSK 3 online: đọc hiểu, nghe và từ vựng trung cấp sơ.',
                'h1' => 'Học tiếng Trung HSK 3',
                'intro' => 'HSK 3 mở khóa giao tiếp linh hoạt hơn — phù hợp sau 1–2 năm học.',
                'features' => [
                    ['icon' => '📖', 'title' => 'Đọc hiểu', 'desc' => 'Bài đọc ngắn'],
                    ['icon' => '👂', 'title' => 'Luyện nghe', 'desc' => 'Phát âm chuẩn'],
                    ['icon' => '📝', 'title' => 'Quiz HSK 3', 'desc' => 'Đề thi thử'],
                ],
            ],
            4 => [
                'title' => 'Học HSK 4 online — Trung cấp',
                'description' => 'Học HSK 4 online với bài học, từ vựng và luyện thi trên 汉越学堂.',
                'h1' => 'Học tiếng Trung HSK 4',
                'intro' => 'HSK 4 tương đương trung cấp — cần cho du học và công việc.',
                'features' => [
                    ['icon' => '💼', 'title' => 'Tiếng Trung công việc', 'desc' => 'Chủ đề thực tế'],
                    ['icon' => '🗺️', 'title' => 'Lộ trình 12 tuần', 'desc' => 'Kế hoạch học'],
                    ['icon' => '🤖', 'title' => 'AI Tutor (Premium)', 'desc' => 'Hỏi đáp tiếng Trung'],
                ],
            ],
            5 => [
                'title' => 'Học HSK 5 online — Cao cấp',
                'description' => 'Luyện HSK 5 online: từ vựng cao cấp, đọc hiểu và luyện thi.',
                'h1' => 'Học tiếng Trung HSK 5',
                'intro' => 'HSK 5 đòi hỏi vốn từ lớn và kỹ năng đọc — luyện có lộ trình.',
                'features' => [
                    ['icon' => '📚', 'title' => 'Từ vựng nâng cao', 'desc' => 'HSK 5–6 trong kho'],
                    ['icon' => '📝', 'title' => 'Đề thi thử', 'desc' => 'Mô phỏng đề thật'],
                    ['icon' => '💡', 'title' => 'Mẹo thi', 'desc' => 'Theo cấp HSK'],
                ],
            ],
            6 => [
                'title' => 'Học HSK 6 online — Cao cấp nhất',
                'description' => 'Học HSK 6 online — cấp cao nhất, luyện thi và từ vựng chuyên sâu.',
                'h1' => 'Học tiếng Trung HSK 6',
                'intro' => 'HSK 6 dành cho người muốn thành thạo — đọc tài liệu phức tạp, giao tiếp sâu.',
                'features' => [
                    ['icon' => '🎯', 'title' => 'Luyện thi chuyên sâu', 'desc' => 'Quiz HSK 6'],
                    ['icon' => '📕', 'title' => 'Từ điển tích hợp', 'desc' => 'Tra nhanh Hán tự'],
                    ['icon' => '👑', 'title' => 'Premium', 'desc' => 'AI & video VIP'],
                ],
            ],
        ],
    ],

    /*
    | Google AdSense (miễn phí) — xem docs/ADS-SETUP.md
    |
    | 1. Đăng ký → lấy mã xác minh → ADSENSE_VERIFICATION
    | 2. Sau duyệt → ca-pub-xxx → ADSENSE_CLIENT_ID + ADS_ENABLED=true
    | 3. Tạo ad unit → số slot → ADSENSE_SLOT_BANNER / VOCAB / QUIZ
    */
    'ads' => [
        'enabled' => filter_var(env('ADS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'client_id' => trim((string) env('ADSENSE_CLIENT_ID', '')),
        'verification' => trim((string) env('ADSENSE_VERIFICATION', '')),
        'auto_ads' => filter_var(env('ADSENSE_AUTO_ADS', true), FILTER_VALIDATE_BOOLEAN),
        'slots' => [
            'banner' => trim((string) env('ADSENSE_SLOT_BANNER', '')),
            'lessons' => trim((string) env('ADSENSE_SLOT_LESSONS', '')),
            'vocab' => trim((string) env('ADSENSE_SLOT_VOCAB', '')),
            'flashcards' => trim((string) env('ADSENSE_SLOT_FLASHCARDS', '')),
            'quiz' => trim((string) env('ADSENSE_SLOT_QUIZ', '')),
            'footer' => trim((string) env('ADSENSE_SLOT_FOOTER', '')),
        ],
    ],

    /*
    | Premium thanh toán
    | sandbox — kích hoạt ngay (dev / demo production)
    | live — cần cấu hình VNPay / Momo (sắp ra mắt)
    */
    'premium' => [
        'payment_mode' => env('PREMIUM_PAYMENT_MODE', 'sandbox'),
    ],
];
