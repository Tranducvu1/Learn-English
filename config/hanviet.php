<?php

return [
    'name' => env('HANVIET_APP_NAME', '汉越学堂'),
    'description' => 'Nền tảng luyện thi HSK & học tiếng Trung online miễn phí',
    'asset_version' => env('HANVIET_ASSET_VERSION', '20260614v16'),
    'data_path' => database_path('data'),

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
