# Review Checklist — Pre-Deploy Render

> Chạy trước khi deploy. Đánh dấu ✅ khi pass.

## A. Dữ liệu SQL (không JSON runtime)

| # | Kiểm tra | Lệnh / Cách |
|---|----------|-------------|
| A1 | DB có đủ seed | `php artisan tinker --execute="echo Word::count()"` → **1200** |
| A2 | Lessons | `Lesson::count()` → **214** |
| A3 | Quizzes | `Quiz::count()` → **86** |
| A4 | Bootstrap API | `curl /api/v1/bootstrap` → vocabulary.words.length = 1200 |
| A5 | Nghĩa tiếng Việt | `php artisan app:enrich-vietnamese` → ≥300 từ có dấu Việt |
| A6 | Cache clear sau seed | `php artisan cache:clear` |

**Nguồn dữ liệu:** `database/data/*.json` → **chỉ dùng seed 1 lần** → SQL tables (`words`, `lessons`, `quizzes`, …). Runtime đọc qua `AppDataController::bootstrap()`.

## B. Tính năng UI (manual test trên :8000)

| # | Trang | Test |
|---|-------|------|
| B1 | Dashboard | HSK cards click → filter lessons |
| B2 | Lessons | Mở bài → hội thoại 甲乙, từ vựng **click được** + 🔊 |
| B3 | Từ vựng | Click row → modal + phát âm; nút 🔊 riêng |
| B4 | Luyện giọng | Đổi HSK, play, prev/next |
| B5 | Flashcard | Flip, đánh giá SRS |
| B6 | Quiz | Làm quiz, chấm điểm |
| B7 | Từ điển | Search, click, audio |
| B8 | Video | Embed YouTube |
| B9 | Premium | Demo premium modal |
| B10 | Auth | Đăng ký / đăng nhập / sync progress |
| B11 | Dark mode | Toggle theme |
| B12 | Mobile | Bottom nav 5 tab |

## C. API & Auth

```bash
bash scripts/test-api.sh http://127.0.0.1:8000
php artisan test
```

| Endpoint | Expect |
|----------|--------|
| `GET /api/health` | 200 ok |
| `GET /api/v1/bootstrap` | Full bundle |
| `POST /api/v1/auth/register` | Token |
| `GET /api/v1/me/progress` | 401 without token |

## D. Bug-prone code (đã review)

| Vùng | Rủi ro | Trạng thái |
|------|--------|------------|
| `playWord` strict id match | Click không phát âm | ✅ Fixed `resolveWord()` |
| Inline onclick | XSS / broken quotes | ✅ Event delegation |
| `vietnamese` = English | UX kém, không tin user | ⚠️ `app:enrich-vietnamese` + UI hint |
| `backend/` path | Server crash | ✅ Removed |
| `npm run start` với `#` | Artisan error | ✅ Fixed |
| Bootstrap cache stale | Data cũ sau seed | `Cache::forget` in seeder |
| Youdao TTS blocked | Không nghe | Fallback browser TTS + toast |
| Lesson vocab without id | Click fail | ✅ `data-hanzi-only` |

## E. Deploy Render

- [ ] `render.yaml` rootDir = `.`
- [ ] `APP_URL` = Render URL
- [ ] Postgres connected
- [ ] `GET /up` returns 200
- [ ] Hard refresh browser after deploy (`Cmd+Shift+R`)

## F. Sau launch (ưu tiên)

1. Bổ sung nghĩa tiếng Việt đầy đủ (CVDICT pipeline)
2. VNPay / Momo payment
3. Admin CMS (Filament)
4. Sentry error tracking
