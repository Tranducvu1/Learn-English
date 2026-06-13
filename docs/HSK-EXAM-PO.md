# PO — Bộ đề HSK 3.0 (9 cấp) | 汉越学堂

> Vai trò: Product Owner · Chuẩn tham chiếu: **HSK 3.0 (2025 syllabus)** · Đối tượng: người Việt luyện thi

---

## 1. Bối cảnh & vấn đề hiện tại

| Hạng mục | Hiện trạng app | Vấn đề |
|----------|----------------|--------|
| Cấp độ | HSK 1–6 | Thiếu **HSK 7–9** (高等) |
| Từ vựng SQL | 1.200 từ (HSK 2.0 ~200/cấp) | Chưa đủ chuẩn HSK 3.0 (500→11.092) |
| Đề quiz | HSK1: **82 đề**, HSK2–3: **2 đề/cấp**, HSK4–6: **0** | **Lệch nặng**, không dùng được cho luyện thi |
| Kỹ năng thi | Chủ yếu trắc nghiệm từ vựng | Thiếu **Nghe / Đọc / Viết / Nói** theo từng cấp |

**Mục tiêu PO:** Bộ đề **9 phần** (HSK 1→9), mỗi cấp có cấu trúc đề **giống thi thật**, số lượng đề **cân bằng**, lộ trình rõ cho người Việt.

---

## 2. Khung HSK 3.0 — 3 tầng, 9 cấp

```
初等 Elementary     → HSK 1, 2, 3
中等 Intermediate   → HSK 4, 5, 6
高等 Advanced       → HSK 7, 8, 9 (1 đề thi, chấm band theo điểm)
```

| Cấp | Giai đoạn | Từ vựng tích lũy (2025) | Kỹ năng thi |
|-----|-----------|-------------------------|-------------|
| **HSK 1** | 初等 | ~500 | Nghe + Đọc |
| **HSK 2** | 初等 | ~1.272 | Nghe + Đọc + Viết (pinyin hỗ trợ) |
| **HSK 3** | 初等 | ~2.245 | Nghe + Đọc + Viết + **Nói** |
| **HSK 4** | 中等 | ~3.245 | Nghe + Đọc + Viết (80 chữ) + Nói |
| **HSK 5** | 中等 | ~4.316 | Nghe + Đọc + Viết (200 chữ) + Nói |
| **HSK 6** | 中等 | ~5.456 | Nghe + Đọc + Viết (300 chữ) + Nói |
| **HSK 7** | 高等 | ~7.408 | Nghe + Đọc + Viết + Nói (band exam) |
| **HSK 8** | 高等 | ~9.071 | Cùng đề HSK 7–9 |
| **HSK 9** | 高等 | ~11.092 | Cùng đề HSK 7–9 |

---

## 3. Ma trận bộ đề — đề xuất PO (MVP → Scale)

### Nguyên tắc chia đề

1. **Mỗi cấp = 1 “học phần”** riêng trên UI (tab HSK 1…9).
2. **Đề mini** (15 câu, 10 phút) — ôn sau mỗi 3–5 bài học.
3. **Đề chuẩn** (40 câu, 35 phút) — mô phỏng format list + reading.
4. **Đề thi thử** (full mock) — trước ngày thi 2–4 tuần.
5. **HSK 7–9:** 1 loại **đề tổng hợp** (không tách 7/8/9), hiển thị band dự đoán.

### Số đề mục tiêu (Free / Premium)

| Cấp | Đề mini (15c) | Đề chuẩn (40c) | Đề thi thử (full) | Tổng | Ghi chú Free |
|-----|---------------|----------------|------------------|------|--------------|
| HSK 1 | 8 | 4 | 2 | **14** | Free: 4 mini + 1 thử |
| HSK 2 | 10 | 5 | 2 | **17** | Free: 5 mini + 1 thử |
| HSK 3 | 12 | 6 | 3 | **21** | Free: 4 mini + 1 thử |
| HSK 4 | 12 | 6 | 3 | **21** | Soft paywall: 2 mini |
| HSK 5 | 10 | 5 | 3 | **18** | Premium chủ yếu |
| HSK 6 | 10 | 5 | 3 | **18** | Premium chủ yếu |
| HSK 7–9 | — | — | 5 (band) | **5** | Premium / beta |
| **Tổng** | 62 | 31 | 21 | **~114 đề** | |

> Hiện có **86 đề** nhưng **82 gói HSK1** → cần **tái phân bổ + sinh thêm** theo ma trận trên.

---

## 4. Cấu trúc câu hỏi theo từng cấp

### HSK 1–2 (Foundational)

| Loại câu | % đề mini | Ví dụ |
|----------|-----------|-------|
| Nghe → chọn hình / từ | 40% | Nghe “你好” → chọn 你好 |
| Đọc → chọn nghĩa Việt | 40% | 谢谢 → Cảm ơn |
| Sắp từ thành câu | 20% | 我 / 叫 / 小明 |

**Không** có câu ngữ pháp khó. Thời gian: **10 phút / 15 câu**.

### HSK 3–4 (Transition)

| Loại câu | % |
|----------|---|
| Nghe hội thoại ngắn | 30% |
| Đọc hiểu đoạn 2–3 câu | 30% |
| Từ vựng trong ngữ cảnh | 25% |
| Viết câu (chọn từ điền) | 15% |

### HSK 5–6 (Intermediate)

| Loại câu | % |
|----------|---|
| Nghe bài giảng / hội thoại dài | 25% |
| Đọc hiểu báo ngắn | 35% |
| Từ đồng nghĩa / collocation | 20% |
| Viết / sắp câu phức | 20% |

### HSK 7–9 (Advanced — 1 đề band)

- Nghe: bài thuyết trình, phỏng vấn
- Đọc: văn học, kinh tế, xã hội Trung Quốc
- Viết: luận 400–600 chữ (app: chấm AI Premium)
- Nói: trình bày quan điểm 2 phút

---

## 5. Ánh xạ nội dung app → đề thi

```
words (SQL)          → câu hỏi từ vựng / điền từ
lesson_dialogues     → câu nghe hội thoại
lesson_word          → đề mini theo bài (1 đề / 3 bài)
quizzes              → đề chuẩn + thi thử
```

**Quy tắc gắn đề với bài học:**

| Cấp | 1 đề mini sau | Nguồn câu |
|-----|---------------|-----------|
| HSK 1 | 3 bài (~18 từ) | Từ bài + dialogue |
| HSK 2 | 3 bài (~24 từ) | Từ bài + dialogue |
| HSK 3–4 | 4 bài (~32 từ) | Từ + câu quiz cũ |
| HSK 5–6 | 5 bài (~50 từ) | Chủ đề chuyên sâu |

---

## 6. Lộ trình triển khai (tránh refactor nhiều lần)

### Phase A — Cấu trúc (1 sprint) ✅ ưu tiên

- [ ] Config `config/hsk_exam.php` — ma trận 9 cấp
- [ ] UI tab **HSK 1–9** + nhóm 初等/中等/高等
- [ ] Tag đề: `mini` | `standard` | `mock` | `band`
- [ ] Tái phân loại 86 đề hiện có (không xóa — gắn `level` + `type`)

### Phase B — Cân bằng đề (2 sprint)

- [ ] Script `generate-quizzes.cjs` sinh đề từ SQL words + dialogues
- [ ] HSK 2–6: đạt tối thiểu 14–21 đề/cấp theo ma trận
- [ ] Giảm HSK1 từ 82 → 14 (gộp câu trùng, chuyển sang HSK2)

### Phase C — Từ vựng HSK 3.0 (3–4 sprint)

- [ ] Nâng word bank: 500 / 1272 / 2245… (theo cấp)
- [ ] Pipeline dịch Việt (CVDICT)
- [ ] Thêm level `hsk7`, `hsk8`, `hsk9` (placeholder + Premium)

### Phase D — Kỹ năng thi thật (Premium)

- [ ] Module Nghe (audio clip)
- [ ] Module Viết (AI chấm)
- [ ] Module Nói HSK 3+ (STT + scoring)

---

## 7. Phân quyền Free vs Premium (PO)

| Cấp | Free | Premium |
|-----|------|---------|
| HSK 1–2 | Tất cả mini + 1 mock/cấp | Full mock + giải thích AI |
| HSK 3 | 4 mini + 1 mock | Full + nói |
| HSK 4–6 | 2 mini preview | Full bank |
| HSK 7–9 | 0 (hoặc 1 sample) | Band mock + lộ trình |

---

## 8. KPI đo lường sau khi có bộ đề 9 phần

- **Completion rate** đề mini ≥ 60%
- **Mock exam** attempt trước thi thật ≥ 1 lần/user
- **Retention D7** sau khi làm đề HSK 1 mock ≥ 35%
- **Conversion** Free→Premium tại HSK 4 soft paywall ≥ 4%

---

## 9. Quyết định PO (tóm tắt)

1. **Chuẩn hóa theo HSK 3.0 9 cấp**, không giữ chỉ HSK 2.0 6 cấp.
2. **114 đề** là mục tiêu scale; MVP **~50 đề cân bằng** (6–10/cấp cho HSK1–6).
3. **HSK 7–9** = 1 track “Band exam”, không tách 3 bộ đề riêng.
4. **Ưu tiên sửa lệch 82 đề HSK1** trước khi thêm từ mới.
5. Mọi đề load từ **SQL**, JSON chỉ dùng seed.

---

*Tài liệu PO — cập nhật khi CTI công bố chi tiết đề mẫu HSK 7–9.*
