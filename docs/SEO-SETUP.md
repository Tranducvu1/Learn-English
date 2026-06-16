# SEO Setup — 汉越学堂

Site: **https://hanviet-3tv7.onrender.com**

## Đã có trong code (Phase 1)

- `sitemap.xml` — `/sitemap.xml`
- `robots.txt` — `/robots.txt` (trỏ tới sitemap)
- Open Graph + Twitter meta trên app và landing
- JSON-LD (`WebApplication`, `Course` trên landing)
- Landing pages (indexable):
  - `/hoc-tieng-trung`
  - `/luyen-thi-hsk`
  - `/tu-vung-hsk`
  - `/hsk-1` … `/hsk-6`
- Deep link app: `/?page=lessons&hsk=1`
- GA4 (khi set env): `GOOGLE_ANALYTICS_ID`

## Bạn cần làm (hợp pháp, miễn phí)

### 1. Google Search Console

1. Vào [Google Search Console](https://search.google.com/search-console)
2. Thêm property: `https://hanviet-3tv7.onrender.com`
3. Xác minh (HTML tag hoặc URL prefix)
4. **Sitemaps** → gửi: `https://hanviet-3tv7.onrender.com/sitemap.xml`
5. **URL inspection** → Request indexing cho `/` và `/hsk-1`

### 2. Google Analytics 4 (tùy chọn)

1. [analytics.google.com](https://analytics.google.com) → tạo property
2. Copy Measurement ID (`G-XXXXXXXX`)
3. Render → Environment → `GOOGLE_ANALYTICS_ID=G-XXXXXXXX`
4. Redeploy

### 3. Custom domain (khuyên dùng sau 30 ngày)

Domain riêng (vd `.vn`) giúp SEO và branding. Cập nhật `APP_URL` trên Render.

## Kiểm tra nhanh

```bash
curl -s https://hanviet-3tv7.onrender.com/sitemap.xml | head
curl -s https://hanviet-3tv7.onrender.com/robots.txt
curl -sI https://hanviet-3tv7.onrender.com/hsk-1 | head -1
```

## Nội dung & backlink

- Chia sẻ landing `/hsk-1` trên group Facebook học Trung
- TikTok bio → link trang chủ
- Không spam — chỉ post có giá trị
