# Deploy lên GitHub Pages

## Bước 1 — Tạo repo GitHub

Trong thư mục project (chỉ folder **Learn Chinese**, không phải cả `/Users/tt`):

```bash
cd "/Users/tt/texttospeech/Learn Chinese"
git init
git add .
git commit -m "Initial commit: 汉越学堂 web học tiếng Trung"
git branch -M main
git remote add origin https://github.com/TEN-BAN/learn-chinese.git
git push -u origin main
```

Thay `TEN-BAN/learn-chinese` bằng repo của bạn.

## Bước 2 — Bật GitHub Pages

1. Vào repo trên GitHub → **Settings** → **Pages**
2. **Build and deployment** → Source: **GitHub Actions**
3. Push lên `main` → workflow **Deploy GitHub Pages** tự chạy

## Bước 3 — Xem site

Sau khi Action xanh (✓):

`https://TEN-BAN.github.io/learn-chinese/`

(URL hiện trong tab **Actions** → run mới nhất → **Deploy** job)

## Test build local

```bash
npm run build:site
npm run preview:site
# Mở http://localhost:8080
```

## Cập nhật data rồi deploy

```bash
# Sửa data/*.json xong:
npm run data:build    # cập nhật js/data-bundle.js
git add .
git commit -m "Update vocabulary"
git push
```

CI tự chạy `build-site.cjs` (gồm `build-data`) rồi deploy `dist/`.

## Lỗi thường gặp

| Lỗi | Cách sửa |
|-----|----------|
| Pages chưa bật | Settings → Pages → Source: **GitHub Actions** |
| Repo nằm trong git cha (`/Users/tt`) | `git init` riêng trong folder Learn Chinese |
| 404 trắng | Đợi 1–2 phút; kiểm tra URL có `/tên-repo/` |
| Youdao TTS không nghe | Cần HTTPS — GitHub Pages OK |

## Deploy lên Vercel

**Quan trọng:** Site production là **HTML + JS tĩnh** (`index.html`, `css/`, `js/`), **không** phải bản Vite/React mặc định.

| Cấu hình | Giá trị |
|----------|---------|
| Build Command | `npm run build` (= `node scripts/build-site.cjs`) |
| Output Directory | `dist` |
| Framework Preset | Other (hoặc để Vercel đọc `vercel.json`) |

Nếu Vercel Dashboard đang set **Build Command** là `tsc -b && vite build`, đổi thành `npm run build` hoặc xóa override để dùng `package.json`.

Sau deploy, trong DevTools **Network** phải thấy `200` cho `/js/app.js`, `/js/data-bundle.js`, … — nếu `404` là đang serve sai bản build.

`content.js` trong console thường là **extension trình duyệt**, không phải lỗi app.

## Files deploy

Workflow: `.github/workflows/deploy.yml` · Vercel: `vercel.json`

Chỉ deploy: `index.html`, `css/`, `js/`, `data/` — không dùng Vite/React trên production (dev React: `npm run build:react`).
