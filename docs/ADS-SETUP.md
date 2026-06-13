# Gắn quảng cáo miễn phí — Google AdSense

> Site: **https://hanviet-3tv7.onrender.com**

## Bước 1 — Đăng ký (free)

1. Vào [https://adsense.google.com](https://adsense.google.com) → **Bắt đầu**
2. Thêm site: `https://hanviet-3tv7.onrender.com`
3. Google cho **mã xác minh** (dạng `ca-pub-xxxxxxxxxxxxxxxx` hoặc meta tag)

## Bước 2 — Xác minh site trên Render

Render Dashboard → **hanviet** → **Environment** → thêm:

| Biến | Ví dụ | Mô tả |
|------|-------|-------|
| `ADSENSE_VERIFICATION` | `ca-pub-1234567890123456` | Mã Google gửi khi đăng ký |

Save → đợi deploy → quay lại AdSense bấm **Xác minh**.

Trang hỗ trợ:
- https://hanviet-3tv7.onrender.com/privacy
- https://hanviet-3tv7.onrender.com/ads.txt

## Bước 3 — Sau khi được duyệt (1–14 ngày)

AdSense → **Tài khoản** → copy **Publisher ID**: `ca-pub-XXXXXXXX`

Render Environment:

```
ADS_ENABLED=true
ADSENSE_CLIENT_ID=ca-pub-XXXXXXXXXXXXXXXX
ADSENSE_AUTO_ADS=true
```

## Bước 4 — Tạo Ad Unit ID (tùy chọn)

AdSense → **Quảng cáo** → **Theo đơn vị quảng cáo** → **Display ads**

| Vị trí | Biến env |
|--------|----------|
| Trang chủ | `ADSENSE_SLOT_BANNER` |
| Từ vựng | `ADSENSE_SLOT_VOCAB` |
| Luyện thi | `ADSENSE_SLOT_QUIZ` |

Copy số **data-ad-slot** (vd `1234567890`) vào Render.

## Biến môi trường đầy đủ

```env
ADS_ENABLED=false
ADSENSE_CLIENT_ID=
ADSENSE_VERIFICATION=
ADSENSE_AUTO_ADS=true
ADSENSE_SLOT_BANNER=
ADSENSE_SLOT_VOCAB=
ADSENSE_SLOT_QUIZ=
```
