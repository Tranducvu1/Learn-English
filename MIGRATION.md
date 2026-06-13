# 汉越学堂 — Laravel Monolith (HOÀN TẤT)

Toàn bộ code nằm trong **repo root** — chuẩn Laravel, không còn thư mục `backend/`.

## Cấu trúc

```
Learn-English/                 ★ Laravel app (php artisan serve)
├── app/                       Controllers, Models, Services
├── resources/
│   ├── views/app.blade.php    SPA 15 trang
│   ├── css/style.css
│   └── js/                    api, app, storage, srs, voice
├── public/                    Published assets + index.php
├── database/data/*.json       Seed content
├── routes/web.php + api.php
├── scripts/                   Data generators
├── review-code/               Dev tool (SSE review)
└── legacy/react-scaffold/     React WIP (không production)
```

## Chạy

```bash
composer install          # lần đầu
npm run setup             # migrate + seed + publish assets
npm run start             # http://localhost:8000
```

## Sửa code

| File | Sau khi sửa |
|------|-------------|
| `resources/views/*.blade.php` | Refresh browser |
| `resources/js/*.js`, `resources/css/` | `npm run publish` |
| `database/data/*.json` | `npm run data:seed` |

## Test

```bash
npm run test:laravel
npm run test:api          # cần server chạy
```

## Deploy

- Docker: `Dockerfile` + `render.yaml` (rootDir: `.`)
- Không còn static GitHub Pages / `data-bundle.js`

## Đã xóa (legacy)

- `backend/` subfolder
- Root `js/`, `css/`, `data/`, `dist/`, `index.html` static
- `app:sync-frontend` → `app:publish-frontend`
