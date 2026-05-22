# 汉越学堂 — Web Học Tiếng Trung

Web học tiếng Trung tĩnh (HTML + CSS + JS + JSON), chạy local không cần build.

## Chạy web (QUAN TRỌNG)

```bash
cd "Learn Chinese"
python3 -m http.server 8080
# hoặc: npx serve .
```

Mở: **http://localhost:8080**

> ⚠️ **Không dùng `npm run dev`** (Vite/React cũ).

### Mở trực tiếp file (double-click `index.html`)

Được — **không cần server** nếu đã có `js/data-bundle.js` (~678KB, gồm 1200 từ + quiz).

Sau khi sửa `data/*.json`, chạy lại: `npm run data:build`

### Lỗi CORS `file://` + fetch

Trình duyệt **cấm** `fetch('data/vocabulary.json')` khi mở `file:///...`. App dùng `data-bundle.js` thay thế — không fetch nữa khi mở file hoặc khi bundle đã đủ dữ liệu.

## Cấu trúc

```
├── index.html          # SPA chính
├── css/style.css       # Giao diện + dark mode + responsive
├── js/
│   ├── app.js          # Logic ứng dụng
│   ├── storage.js      # LocalStorage (streak, SRS, premium)
│   └── srs.js          # Spaced Repetition
└── data/
    ├── lessons.json    # Bài học HSK + chủ đề
    ├── vocabulary.json # Từ vựng
    ├── quizzes.json    # Quiz
    ├── dictionary.json # Từ điển
    ├── videos.json     # Video YouTube
    └── premium.json    # Gói Premium
```

## Dữ liệu

| File | Nội dung |
|------|----------|
| `data/vocabulary.json` | **1200 từ** HSK (nguồn: complete-hsk-vocabulary) |
| `data/quizzes.json` | **86 đề**, ~1200+ câu hỏi |
| `data/dictionary.json` | 1200 mục tra cứu |
| `data/videos.json` | Playlist embed + 13 video YouTube thật |

Tạo lại từ vựng: `npm run data:vocab`

## Chức năng Free

- **1200+ từ vựng** — trang Từ vựng (lọc HSK, tìm kiếm, phân trang)
- **Luyện giọng** — TTS Hán tự chậm/nhanh, phát cả danh sách
- Bài học HSK 1-6
- Flashcard + SRS cơ bản
- 4 kỹ năng: Nghe, Đọc (ruby pinyin), Viết, Nói (ghi âm)
- Quiz & kiểm tra
- Từ điển tra cứu
- **Video** — nhúng full playlist HSK + 13 bài YouTube
- Nhật ký: streak, từ đã học, tiến độ HSK
- Dark mode

## 4 Tính năng Premium

1. **AI Chinese Tutor** — Chat demo (kết nối API sau)
2. **Phát âm chuyên sâu** — Ghi âm + điểm demo
3. **Lộ trình cá nhân AI** — Learning path demo
4. **Video độc quyền + Offline** — Kho video trong `videos.json`

Nhấn **"Dùng thử Demo"** trong modal Premium để test.

## Cập nhật video YouTube

Playlist: https://www.youtube.com/playlist?list=PLWXyZU_NJb_chvMZ13hgOPB3Vcz7xhW3q

Trong `data/videos.json`, thay `PLACEHOLDER_XX` bằng `youtubeId` thật (phần sau `watch?v=`):

```json
{
  "youtubeId": "McZW0iDsZns",
  "title": "Bài 1 — Giới thiệu khóa học HSK",
  "free": true
}
```

## Thêm dữ liệu

- **Từ mới:** `data/vocabulary.json` → thêm `id`, liên kết `vocabIds` trong `lessons.json`
- **Bài học:** `data/lessons.json` → thêm vào `levels[].lessons`
- **Quiz:** `data/quizzes.json` → `lessonId` khớp với bài học
- **Từ điển:** `data/dictionary.json`

## Tech stack hiện tại vs React

Project gốc có Vite + React (`src/`). Web tĩnh mới dùng `index.html` ở root — không ảnh hưởng React nếu chạy `npm run dev` (sẽ conflict vì cùng `index.html`). Để dev React, đổi tên `index.html` → `index-static.html` hoặc chạy web tĩnh qua server riêng.
