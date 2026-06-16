<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chính sách quyền riêng tư — {{ $appName }}</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ config('hanviet.asset_version') }}" />
  <style>
    .legal-page { max-width: 720px; margin: 0 auto; padding: 48px 20px 80px; }
    .legal-page h1 { font-size: 1.75rem; margin-bottom: 8px; }
    .legal-page h2 { font-size: 1.1rem; margin: 28px 0 10px; }
    .legal-page p, .legal-page li { color: var(--ink-muted); line-height: 1.7; margin-bottom: 10px; }
    .legal-page ul { padding-left: 1.25rem; }
    .legal-back { display: inline-block; margin-bottom: 24px; color: var(--primary); font-weight: 600; }
  </style>
</head>
<body>
  <main class="legal-page">
    <a class="legal-back" href="/">← Về {{ $appName }}</a>
    <h1>Chính sách quyền riêng tư</h1>
    <p>Cập nhật: {{ date('d/m/Y') }}</p>

    <p>{{ $appName }} ({{ $siteUrl }}) là nền tảng học tiếng Trung và luyện thi HSK miễn phí.</p>

    <h2>1. Dữ liệu thu thập</h2>
    <ul>
      <li>Thông tin tài khoản (email, tên) nếu bạn đăng ký</li>
      <li>Tiến độ học, flashcard, điểm quiz — lưu trên trình duyệt và/hoặc máy chủ</li>
      <li>Cookie và dữ liệu phân tích từ bên thứ ba (Google AdSense, Google Analytics) nếu được bật</li>
    </ul>

    <h2>2. Quảng cáo (Google AdSense)</h2>
    <p>Chúng tôi có thể hiển thị quảng cáo qua Google AdSense. Google có thể dùng cookie để hiển thị quảng cáo phù hợp. Bạn có thể tắt quảng cáo cá nhân hóa tại <a href="https://adssettings.google.com" target="_blank" rel="noopener">Cài đặt quảng cáo Google</a>.</p>
    <p>Thông tin thêm: <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">Cách Google dùng cookie trong quảng cáo</a>.</p>

    <p>Thông tin thêm: <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">Cách Google dùng cookie trong quảng cáo</a>.</p>

    <h2>3. Phân tích (Google Analytics)</h2>
    <p>Nếu bật, chúng tôi dùng Google Analytics 4 để đếm lượt truy cập và cải thiện dịch vụ. Google có thể xử lý dữ liệu theo <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">chính sách của Google</a>. Bạn có thể dùng <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener">tiện ích từ chối Analytics</a>.</p>

    <h2>4. Mục đích sử dụng</h2>
    <p>Cung cấp tính năng học, đồng bộ tiến độ, cải thiện trải nghiệm và vận hành dịch vụ miễn phí.</p>

    <h2>5. Liên hệ</h2>
    <p>Câu hỏi về quyền riêng tư: gửi qua kênh hỗ trợ của dự án hoặc issue trên GitHub.</p>
  </main>
</body>
</html>
