# 汉越学堂 — Master Plan (CTO + PO)

> Laravel backend + MySQL + Render + AI Premium | Cập nhật: 2026-06-13

## 1. Tầm nhìn sản phẩm

**Đối tượng:** Người Việt học tiếng Trung (HSK 1–6), 18–35 tuổi, mobile-first.

**Moat:** Nội dung HSK + tiếng Việt giải thích + AI tutor biết ngữ cảnh HSK + chấm phát âm tiếng Trung.

**Monetization:** Freemium — free đủ học cơ bản, premium cho AI + personalization + exclusive content.

---

## 2. PO Review — Free vs Premium

### FREE (giữ user, SEO, viral)

| Chức năng | Lý do free |
|-----------|------------|
| Dashboard + streak cơ bản | Hook hàng ngày |
| HSK 1–2 lessons (45+38 bài) | Đủ demo giá trị |
| Từ vựng 1.200 từ (xem + TTS) | SEO "từ vựng HSK" |
| Flashcard SRS cơ bản | Retention |
| Quiz HSK 1–2 (subset ~30%) | Lead magnet |
| Từ điển tra cứu | Traffic organics |
| Video free playlist YouTube | Content marketing |
| Voice practice TTS (Youdao) | Khác biệt vs app Trung |

### PREMIUM (99k/tháng, 790k/năm)

| Feature ID | Chức năng | Giá trị | Chi phí AI |
|------------|-----------|---------|------------|
| `ai-tutor` | Chat AI tiếng Trung, sửa ngữ pháp, role-play | ★★★★★ | Cao — gate bắt buộc |
| `pronunciation` | Chấm phát âm, shadowing, waveform | ★★★★★ | Cao (Whisper + scoring) |
| `personalized` | Learning path tuần, SRS thông minh, weak-word chart | ★★★★ | Trung bình |
| `exclusive` | Video premium, offline, live speaking room | ★★★ | Thấp (content lock) |

### Soft paywall (free thử, premium unlock full)

| Chức năng | Free limit | Premium |
|-----------|------------|---------|
| HSK 3–6 lessons | Xem 2 bài/level | Full 214 bài |
| Quiz HSK 3–6 | 1 quiz/level | Full 86 quiz |
| AI Tutor | 3 tin nhắn/ngày (demo) | Unlimited |
| Speak scoring | Demo 1 lần/ngày | Unlimited |
| Video premium playlist | Preview 30s | Full |

---

## 3. Marketing funnel

```
SEO (từ vựng HSK, luyện thi) → Free app → Streak 7 ngày
    → Soft paywall HSK3 → Email/onboarding → Trial 7 ngày Premium
    → VNPay 99k/tháng
```

**Kênh ưu tiên:**
1. TikTok/Reels — "30 giây học 1 từ HSK" + link app
2. Facebook group học tiếng Trung VN
3. SEO blog: `/luyen-hsk`, `/tu-vung-hsk` (React routes đã plan)
4. Referral: mời bạn → +7 ngày premium

---

## 4. Kiến trúc kỹ thuật

```
┌─────────────────┐     REST/SSE      ┌──────────────────────┐
│  Frontend SPA   │ ◄──────────────► │  Laravel API         │
│  (index.html /  │                   │  /api/v1/*           │
│   React future) │                   │  Sanctum auth        │
└─────────────────┘                   └──────────┬───────────┘
                                                 │
                    ┌────────────────────────────┼────────────────┐
                    ▼                            ▼                ▼
              MySQL (local)              PostgreSQL (Render)   OpenAI API
              XAMPP 3306                 Blueprint deploy      AI Tutor + STT
```

### Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13, PHP 8.4, Sanctum |
| DB local | MySQL 8 (`hanviet`) |
| DB prod | PostgreSQL (Render free tier) |
| Deploy | Docker + render.yaml (pattern azrun) |
| Realtime | SSE `/api/v1/events/stream` |
| AI | OpenAI-compatible API (GPT-4o-mini chat, Whisper STT) |
| Payment | VNPay (phase 2, pattern azrun) |

---

## 5. Database schema (đã migrate)

**Content:** levels, topics, words, lessons, dialogues, quizzes, videos, premium_plans/features

**User:** users (+ is_premium, streak), user_settings, lesson_progress, hsk_progress, srs_cards, quiz_attempts, study_logs, subscriptions

**New (phase này):** ai_chat_sessions, ai_chat_messages, pronunciation_attempts, review_events

---

## 6. API endpoints

### Public
- `GET /api/v1/levels`, `/lessons`, `/words`, `/quizzes`, `/videos`, `/dictionary`, `/premium`
- `POST /api/v1/auth/register`, `/auth/login`

### Authenticated (Sanctum)
- `GET/PUT /api/v1/me/progress` — sync localStorage → MySQL
- `POST /api/v1/me/lessons/{id}/complete`
- `POST /api/v1/me/srs/review`
- `POST /api/v1/me/quiz/{id}/submit`

### Premium (`middleware: premium`)
- `POST /api/v1/ai/chat` — AI tutor conversation
- `POST /api/v1/ai/speech/transcribe` — audio → text (Whisper)
- `POST /api/v1/ai/speech/score` — pronunciation scoring
- `GET /api/v1/ai/roleplay/{scenario}` — start role-play session

### Review events (SSE)
- `GET /api/v1/events/stream` — SSE client subscribe
- `POST /api/v1/review/trigger` — chạy review-code, broadcast findings

---

## 7. Render deployment

1. Push repo → Render Dashboard → New Blueprint
2. `render.yaml` tạo DB + web service (Docker)
3. Env: `OPENAI_API_KEY`, `APP_URL`, `FRONTEND_URL`
4. Entrypoint: migrate + seed + serve

**URLs:**
- API: `https://hanviet-api.onrender.com`
- Frontend: GitHub Pages / Vercel (env `VITE_API_URL`)

---

## 8. Roadmap triển khai

| Phase | Scope | Status |
|-------|-------|--------|
| **P0** | Laravel API content + MySQL seed | ✅ Done |
| **P1** | Auth Sanctum + progress sync | 🔄 This sprint |
| **P2** | Premium middleware + AI chat + speech | 🔄 This sprint |
| **P3** | SSE review events + Docker/Render | 🔄 This sprint |
| **P4** | Frontend API client (`js/api.js`) | 🔄 This sprint |
| **P5** | VNPay subscription | Backlog |
| **P6** | React migration + SSR SEO | Backlog |

---

## 9. Quyết định CTO (tránh refactor)

1. **Giữ frontend static** — API layer tách biệt, không rewrite UI ngay
2. **MySQL local, Postgres Render** — Laravel abstraction, không raw SQL
3. **Sanctum token** — không session cookie cho SPA cross-origin
4. **SSE thay WebSocket** — đủ cho review events, không cần Reverb free tier
5. **AI fallback** — nếu không có OPENAI_API_KEY, trả canned response (dev mode)
6. **Premium check** — `users.is_premium` OR active `subscriptions.ends_at > now()`

---

## 10. Env cần thiết

```env
# backend/.env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hanviet
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
FRONTEND_URL=http://localhost:8080

# Render production
DB_CONNECTION=pgsql
DATABASE_URL=postgres://...
```
