# 汉越学堂 — Học tiếng Trung & Luyện thi HSK

**Laravel full-stack monolith** — API + Blade SPA + SQLite/MySQL.

## Quick start (Laravel — không cần Vite)

App chạy **Laravel monolith**: Blade + JS tĩnh copy sang `public/`. Không dùng `vite dev` cho web chính.

```bash
composer install
npm run setup      # migrate + seed SQL + enrich VI + publish
npm run enrich:vi  # bổ sung nghĩa tiếng Việt từ dictionary SQL
npm run publish    # sau khi sửa resources/css hoặc resources/js
npm run start      # = php artisan serve → http://127.0.0.1:8000
```

**Dữ liệu:** JSON trong `database/data/` chỉ dùng **seed vào SQL**. App load qua `GET /api/v1/bootstrap` (Laravel cache + PostgreSQL/SQLite).

Hoặc trực tiếp:

```bash
php artisan serve
```

### Lưu ý

- **Không** `cd backend` — thư mục đã gỡ, Laravel nằm ở **root** repo.
- **Không** `npm run dev` / Vite cho SPA chính (Vite chỉ còn cho `legacy/react-scaffold` nếu cần).
- Port 8000 bận → `php artisan serve --port=8003` hoặc tắt process cũ: `lsof -nP -iTCP:8000 -sTCP:LISTEN`
- Đừng gõ comment sau lệnh npm: `npm run start` (không thêm `# :8000` vào script).

## Docs

- [REVIEW-CHECKLIST.md](REVIEW-CHECKLIST.md) — checklist trước deploy Render
- [DEPLOY.md](DEPLOY.md) — deploy Render (không Vercel)
- [docs/HSK-EXAM-PO.md](docs/HSK-EXAM-PO.md) — PO: bộ đề HSK 3.0 (9 cấp)
- `php artisan route:list` — xem routes

## Stack

- Laravel 13 + Sanctum
- Blade SPA (`resources/views/app.blade.php`)
- Vanilla JS (`resources/js/`)
- 1,200 từ vựng, 214 bài học, 86 quiz (DB seed)
