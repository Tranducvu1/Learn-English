<?php

/**
 * Mẹo luyện thi HSK — nội dung PO, UI đọc qua bootstrap.
 */
return [
    'general' => [
        [
            'icon' => '🎯',
            'title' => 'Đặt mục tiêu điểm trước',
            'body' => 'HSK 1–2: nhắm 180+/200. HSK 3–4: 210+/300. HSK 5–6: 280+/300. Lập lịch ôn 6–8 tuần trước ngày thi.',
        ],
        [
            'icon' => '⏱️',
            'title' => 'Luyện đúng thời gian thi',
            'body' => 'Mỗi đề mini 10 phút, đề chuẩn 35 phút. Dùng đồng hồ — không dừng giữa chừng. Quen áp lực thời gian = điểm cao hơn 15–20%.',
        ],
        [
            'icon' => '🔄',
            'title' => 'Ôn sai → làm lại',
            'body' => 'Ghi nhật ký câu sai theo kỹ năng (Nghe/Đọc/Viết). Tuần cuối chỉ ôn nhóm yếu, không học từ mới.',
        ],
        [
            'icon' => '📱',
            'title' => 'Nghe mỗi ngày 15 phút',
            'body' => 'Podcast HSK, video bài giảng, shadowing. Tai quen thanh điệu → phần Nghe không bị “đoán mò”.',
        ],
    ],
    'skills' => [
        'listen' => [
            'title' => 'Kỹ năng Nghe',
            'tips' => [
                'Đọc câu hỏi trước khi nghe — biết cần bắt từ khóa gì.',
                'Ghi chú nhanh số / tên / thời gian nghe được.',
                'Không sửa đáp án liên tục — tin lựa chọn đầu nếu đã nghe kỹ.',
            ],
        ],
        'read' => [
            'title' => 'Kỹ năng Đọc',
            'tips' => [
                'Đọc lướt đoạn văn, quay lại câu hỏi — đừng dịch từng từ.',
                'Chú ý từ đồng nghĩa trong đề (大/很大, 能/可以).',
                'Câu sắp xếp từ: tìm chủ ngữ + động từ trước.',
            ],
        ],
        'write' => [
            'title' => 'Kỹ năng Viết',
            'tips' => [
                'HSK 2+: luyện viết Hán tự 20 từ/ngày — nhớ bộ thủ.',
                'Viết pinyin trước, kiểm tra thanh điệu, rồi mới chuyển Hán tự.',
                'Câu mẫu ngắn an toàn hơn câu dài sai ngữ pháp.',
            ],
        ],
        'speak' => [
            'title' => 'Kỹ năng Nói',
            'tips' => [
                'Trả lời 2–3 câu, không im lặng — điểm fluency quan trọng.',
                'Dùng cấu trúc quen: 我觉得… / 因为…所以… / 虽然…但是…',
                'Ghi âm lại, so với mẫu — Premium AI chấm phát âm.',
            ],
        ],
    ],
    'levels' => [
        'hsk1' => [
            'target_score' => '≥ 180/200',
            'prep_weeks' => '4–6 tuần',
            'focus' => '150 từ lõi + nghe chào hỏi, số, thời gian',
            'mistakes' => ['Nhầm 的/得/地', 'Không phân biệt 二 và 两', 'Bỏ qua thanh 3'],
            'daily_plan' => '20 từ flashcard + 1 bài học + 1 đề mini',
        ],
        'hsk2' => [
            'target_score' => '≥ 180/200',
            'prep_weeks' => '6–8 tuần',
            'focus' => 'Mẫu câu giao tiếp + viết pinyin đúng thanh',
            'mistakes' => ['Nhầm 在/再', 'Không học lượng từ (个/本/张)', 'Nghe bỏ dấu hỏi'],
            'daily_plan' => '25 từ + 1 bài + 2 đề mini/tuần',
        ],
        'hsk3' => [
            'target_score' => '≥ 210/300',
            'prep_weeks' => '8–10 tuần',
            'focus' => 'Hội thoại dài + bắt đầu luyện Nói',
            'mistakes' => ['Chưa quen đề kết hợp Nghe-Đọc', 'Viết sai chữ đơn giản', 'Bỏ phần Nói'],
            'daily_plan' => '30 từ + 2 bài/tuần + 1 mock/tháng',
        ],
        'hsk4' => [
            'target_score' => '≥ 210/300',
            'prep_weeks' => '10–12 tuần',
            'focus' => 'Đọc hiểu đoạn văn + viết 80 chữ',
            'mistakes' => ['Đọc dịch word-by-word', 'Không luyện viết giới hạn thời gian', 'Từ vựng trừu tượng yếu'],
            'daily_plan' => '40 từ + đọc 1 đoạn + 1 đề chuẩn/tuần',
        ],
        'hsk5' => [
            'target_score' => '≥ 280/300',
            'prep_weeks' => '12–16 tuần',
            'focus' => 'Từ HSK5 + viết luận 200 chữ + nghe hội thoại dài',
            'mistakes' => ['Học từ lẻ không theo chủ đề', 'Bỏ luyện viết', 'Không làm full mock'],
            'daily_plan' => '50 từ + 1 mock/2 tuần + sửa bài viết',
        ],
        'hsk6' => [
            'target_score' => '≥ 280/300',
            'prep_weeks' => '16–20 tuần',
            'focus' => 'Đọc báo Trung đơn giản + viết 300 chữ + từ Hán cổ đại',
            'mistakes' => ['Chỉ học từ, không đọc văn bản dài', 'Không ôn ngữ pháp nâng cao', 'Thiếu stamina 3 giờ'],
            'daily_plan' => '60 từ + đọc + 1 mock/tháng',
        ],
        'hsk7' => [
            'target_score' => 'Band 7+',
            'prep_weeks' => '20+ tuần',
            'focus' => 'Đề tổng hợp 高等 — đọc học thuật, viết luận, nói trôi chảy',
            'mistakes' => ['Học như HSK 6', 'Không luyện đề band', 'Bỏ qua từ chuyên ngành'],
            'daily_plan' => 'Đọc + nghe native + mock band',
        ],
    ],
];
