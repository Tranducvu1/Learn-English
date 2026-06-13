<?php

/**
 * Ma trận bộ đề HSK 3.0 — 9 cấp (PO spec).
 * UI + generator đọc file này, không hardcode trong JS.
 */
return [
    'version' => 'hsk3.0',
    'stages' => [
        'elementary' => [
            'label' => '初等 — Sơ cấp',
            'label_vi' => 'Sơ cấp',
            'levels' => ['hsk1', 'hsk2', 'hsk3'],
        ],
        'intermediate' => [
            'label' => '中等 — Trung cấp',
            'label_vi' => 'Trung cấp',
            'levels' => ['hsk4', 'hsk5', 'hsk6'],
        ],
        'advanced' => [
            'label' => '高等 — Cao cấp',
            'label_vi' => 'Cao cấp',
            'levels' => ['hsk7', 'hsk8', 'hsk9'],
            'band_exam' => true,
        ],
    ],

    'levels' => [
        'hsk1' => [
            'num' => 1,
            'name' => 'HSK 1',
            'name_vi' => 'HSK 1 — Sơ cấp',
            'color' => '#22c55e',
            'vocab_target' => 500,
            'skills' => ['listen', 'read'],
            'exam_types' => [
                'mini' => ['count' => 8, 'questions' => 15, 'minutes' => 10, 'free' => 4],
                'standard' => ['count' => 4, 'questions' => 40, 'minutes' => 35, 'free' => 0],
                'mock' => ['count' => 2, 'questions' => 40, 'minutes' => 40, 'free' => 1],
            ],
            'status' => 'live',
        ],
        'hsk2' => [
            'num' => 2,
            'name' => 'HSK 2',
            'name_vi' => 'HSK 2 — Sơ cấp',
            'color' => '#3b82f6',
            'vocab_target' => 1272,
            'skills' => ['listen', 'read', 'write'],
            'exam_types' => [
                'mini' => ['count' => 10, 'questions' => 15, 'minutes' => 10, 'free' => 5],
                'standard' => ['count' => 5, 'questions' => 40, 'minutes' => 35, 'free' => 0],
                'mock' => ['count' => 2, 'questions' => 40, 'minutes' => 40, 'free' => 1],
            ],
            'status' => 'live',
        ],
        'hsk3' => [
            'num' => 3,
            'name' => 'HSK 3',
            'name_vi' => 'HSK 3 — Sơ cấp',
            'color' => '#8b5cf6',
            'vocab_target' => 2245,
            'skills' => ['listen', 'read', 'write', 'speak'],
            'exam_types' => [
                'mini' => ['count' => 12, 'questions' => 15, 'minutes' => 12, 'free' => 4],
                'standard' => ['count' => 6, 'questions' => 40, 'minutes' => 35, 'free' => 0],
                'mock' => ['count' => 3, 'questions' => 45, 'minutes' => 45, 'free' => 1],
            ],
            'status' => 'live',
        ],
        'hsk4' => [
            'num' => 4,
            'name' => 'HSK 4',
            'name_vi' => 'HSK 4 — Trung cấp',
            'color' => '#f59e0b',
            'vocab_target' => 3245,
            'skills' => ['listen', 'read', 'write', 'speak'],
            'exam_types' => [
                'mini' => ['count' => 12, 'questions' => 15, 'minutes' => 12, 'free' => 2],
                'standard' => ['count' => 6, 'questions' => 40, 'minutes' => 40, 'free' => 0],
                'mock' => ['count' => 3, 'questions' => 50, 'minutes' => 50, 'free' => 0],
            ],
            'status' => 'partial',
        ],
        'hsk5' => [
            'num' => 5,
            'name' => 'HSK 5',
            'name_vi' => 'HSK 5 — Trung cấp',
            'color' => '#ec4899',
            'vocab_target' => 4316,
            'skills' => ['listen', 'read', 'write', 'speak'],
            'exam_types' => [
                'mini' => ['count' => 10, 'questions' => 15, 'minutes' => 12, 'free' => 0],
                'standard' => ['count' => 5, 'questions' => 45, 'minutes' => 45, 'free' => 0],
                'mock' => ['count' => 3, 'questions' => 55, 'minutes' => 55, 'free' => 0],
            ],
            'status' => 'partial',
        ],
        'hsk6' => [
            'num' => 6,
            'name' => 'HSK 6',
            'name_vi' => 'HSK 6 — Trung cấp',
            'color' => '#dc2626',
            'vocab_target' => 5456,
            'skills' => ['listen', 'read', 'write', 'speak'],
            'exam_types' => [
                'mini' => ['count' => 10, 'questions' => 15, 'minutes' => 12, 'free' => 0],
                'standard' => ['count' => 5, 'questions' => 45, 'minutes' => 45, 'free' => 0],
                'mock' => ['count' => 3, 'questions' => 60, 'minutes' => 60, 'free' => 0],
            ],
            'status' => 'partial',
        ],
        'hsk7' => [
            'num' => 7,
            'name' => 'HSK 7',
            'name_vi' => 'HSK 7 — Cao cấp (Band)',
            'color' => '#7c3aed',
            'vocab_target' => 7408,
            'skills' => ['listen', 'read', 'write', 'speak'],
            'exam_types' => [
                'band' => ['count' => 5, 'questions' => 80, 'minutes' => 120, 'free' => 0],
            ],
            'status' => 'planned',
            'band_group' => 'hsk7-9',
        ],
        'hsk8' => [
            'num' => 8,
            'name' => 'HSK 8',
            'name_vi' => 'HSK 8 — Cao cấp (Band)',
            'color' => '#6d28d9',
            'vocab_target' => 9071,
            'skills' => ['listen', 'read', 'write', 'speak'],
            'exam_types' => [
                'band' => ['count' => 5, 'questions' => 80, 'minutes' => 120, 'free' => 0],
            ],
            'status' => 'planned',
            'band_group' => 'hsk7-9',
        ],
        'hsk9' => [
            'num' => 9,
            'name' => 'HSK 9',
            'name_vi' => 'HSK 9 — Cao cấp (Band)',
            'color' => '#5b21b6',
            'vocab_target' => 11092,
            'skills' => ['listen', 'read', 'write', 'speak'],
            'exam_types' => [
                'band' => ['count' => 5, 'questions' => 80, 'minutes' => 120, 'free' => 0],
            ],
            'status' => 'planned',
            'band_group' => 'hsk7-9',
        ],
    ],
];
