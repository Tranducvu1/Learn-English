<?php

/**
 * Lộ trình học HSK chuẩn — free tier hiển thị; Premium cá nhân hóa qua AI.
 */
return [
    'title' => 'Lộ trình HSK 3.0 cho người Việt',
    'subtitle' => '12 tuần từ zero → sẵn sàng thi HSK 1, mở rộng đến HSK 6',
    'phases' => [
        [
            'id' => 'foundation',
            'weeks' => 'Tuần 1–2',
            'title' => 'Nền tảng & phát âm',
            'color' => '#22c55e',
            'goals' => ['Làm quen thanh điệu 4 thanh + thanh nhẹ', '100 từ HSK1', 'Đọc được pinyin cơ bản'],
            'tasks' => [
                ['action' => 'vocabulary', 'label' => 'Học 10 từ/ngày — chủ đề chào hỏi'],
                ['action' => 'lessons', 'label' => 'Hoàn thành 4 bài HSK 1 đầu'],
                ['action' => 'flashcards', 'label' => 'SRS: ôn mỗi sáng 10 phút'],
            ],
        ],
        [
            'id' => 'hsk1-core',
            'weeks' => 'Tuần 3–4',
            'title' => 'HSK 1 — lõi thi',
            'color' => '#22c55e',
            'goals' => ['500 từ mục tiêu HSK 3.0', 'Làm quen đề mini 15 câu', 'Nghe chào hỏi, mua hàng'],
            'tasks' => [
                ['action' => 'quiz', 'label' => '3 đề mini HSK 1 (mục tiêu ≥ 12/15)'],
                ['action' => 'lessons', 'label' => 'Xong toàn bộ bài HSK 1'],
                ['action' => 'exam-tips', 'label' => 'Đọc mẹo thi HSK 1'],
            ],
        ],
        [
            'id' => 'hsk2-bridge',
            'weeks' => 'Tuần 5–8',
            'title' => 'HSK 2 — mở rộng',
            'color' => '#3b82f6',
            'goals' => ['Giao tiếp hàng ngày', 'Viết pinyin + Hán tự đơn giản', 'Đề mini + 1 mock'],
            'tasks' => [
                ['action' => 'vocabulary', 'label' => 'Từ vựng HSK 2 theo chủ đề'],
                ['action' => 'quiz', 'label' => '1 đề thi thử HSK 2'],
                ['action' => 'videos', 'label' => 'Xem 2 video/ngày có phụ đề'],
            ],
        ],
        [
            'id' => 'hsk3-plus',
            'weeks' => 'Tuần 9–12',
            'title' => 'HSK 3+ & luyện thi',
            'color' => '#8b5cf6',
            'goals' => ['4 kỹ năng: Nghe Đọc Viết Nói', 'Mock định kỳ', 'Ổn định streak 30 ngày'],
            'tasks' => [
                ['action' => 'quiz', 'label' => 'Đề chuẩn 40 câu — timing 35 phút'],
                ['action' => 'journal', 'label' => 'Theo dõi điểm yếu trên Tiến độ'],
                ['action' => 'premium', 'label' => 'Premium: AI RAG + video VIP + không quảng cáo'],
            ],
        ],
    ],
    'premium_upsell' => [
        'title' => 'Premium — lộ trình AI cá nhân',
        'points' => [
            'AI phân tích từ yếu, quiz sai → kế hoạch tuần',
            'Gia sư RAG tra từ điển + bài học trong app',
            'Video VIP + không quảng cáo',
        ],
    ],
];
