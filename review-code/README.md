# Code Review (local + SSE server)

Review trực tiếp trên codebase — **không cần server** cho scan local.

Kết hợp Laravel SSE để nhận events realtime khi CI/admin trigger review.

## Chạy review local

```bash
cd review-code
npm install
npm run review
```

Review 1 file cụ thể:

```bash
npm run review -- js/app.js
```

## Lắng nghe events từ Laravel (SSE)

Terminal 1 — API backend:
```bash
cd ../backend && php artisan serve
```

Terminal 2 — SSE client:
```bash
npm run listen
# hoặc: HANVIET_API_URL=https://hanviet-api.onrender.com npm run listen
```

Terminal 3 — trigger review (broadcast qua SSE):
```bash
curl -X POST http://localhost:8000/api/v1/review/trigger
curl -X POST http://localhost:8000/api/v1/review/trigger -H "Content-Type: application/json" -d '{"file":"js/app.js"}'
```

## Rules

| Rule | Mức | Mô tả |
|------|-----|--------|
| `no-console` | warning | console.log/debug còn sót |
| `no-debugger` | error | debugger statement |
| `no-eval` | critical | eval() |
| `unsafe-innerhtml` | warning | innerHTML — XSS risk |
| `ts-ignore` | warning | @ts-ignore / @ts-nocheck |
| `todo-fixme` | info | TODO/FIXME chưa xử lý |
| `large-file` | info/warning | File > 400 dòng |
| `duplicate-stack` | error/warning | js/ legacy vs src/ React |
| `any-type` | info | Dùng `any` trong TS |

Exit code `1` nếu có **error** hoặc **critical**.
