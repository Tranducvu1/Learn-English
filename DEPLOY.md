# Deploy 汉越学堂 lên Render

> **Laravel full-stack monolith** — không dùng Vercel / GitHub Pages / Vite cho production.

## Kiến trúc production

```
Render Web Service (Docker)
├── Laravel 13 (php artisan serve :10000)
├── Blade SPA + public/js + public/css
├── PostgreSQL (Render managed DB)
└── API /api/v1/bootstrap ← load từ SQL, không JSON tĩnh
```

## Chuẩn bị local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan app:enrich-vietnamese   # bổ sung nghĩa tiếng Việt từ dictionary SQL
npm run publish
php artisan test
bash scripts/test-api.sh http://127.0.0.1:8000
```

## Deploy bằng Blueprint

1. Push repo lên GitHub
2. [Render Dashboard](https://dashboard.render.com) → **New** → **Blueprint**
3. Chọn repo — Render đọc `render.yaml` ở **root** (không có `backend/`)
4. Set secret `OPENAI_API_KEY` nếu dùng AI Tutor
5. Deploy xong → URL dạng `https://hanviet-api.onrender.com`

## Biến môi trng (Render)

| Biến | Giá trị |
|------|---------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Generate (Render) |
| `APP_URL` | URL service Render |
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | Từ Render Postgres |
| `CACHE_STORE` | `database` |
| `SESSION_DRIVER` | `cookie` |

## Sau mỗi lần sửa CSS/JS

```bash
npm run publish
git add public/css public/js
git commit -m "Publish frontend assets"
git push
```

Hoặc thêm vào `docker/entrypoint.sh` (đã có `app:publish-frontend`).

## Health check

- `GET /up` — Laravel health
- `GET /api/health` — API JSON
- `GET /api/v1/bootstrap` — phải trả 1200 từ, 214 bài, 86 quiz

## Không dùng

| Cũ | Lý do |
|----|-------|
| Vercel | SPA tĩnh, không có Laravel/SQL |
| GitHub Pages | Không API, không auth |
| `cd backend` | Đã gỡ — Laravel ở root |
| `npm run dev` (Vite) | Chỉ legacy React scaffold |

## Free tier Render

- Service sleep sau 15 phút không traffic → cold start ~30s
- Postgres free expire sau 90 ngày — backup `php artisan db:seed` + `database/data/*.json`

## Rollback

Render Dashboard → Deploys → chọn bản trước → **Rollback**
