<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  @php
    $appName = $appName ?? config('hanviet.name');
    $seoTitle = $title;
    $seoDescription = $description;
    $seoCanonical = $canonical;
    $seoOgImage = $ogImage;
  @endphp
  <title>{{ $title }}</title>
  @include('partials.seo-meta')
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90' fill='%2316a34a'>中</text></svg>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ config('hanviet.asset_version') }}" />
  @include('partials.analytics')
  @if(!empty($jsonLd))
  <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
  @endif
</head>
<body class="landing-body">
  <a class="skip-link" href="#landingMain">Chuyển tới nội dung chính</a>
  <header class="landing-header">
    <div class="landing-header-inner">
      <a href="{{ url('/') }}" class="brand">
        <span class="brand-mark">中</span>
        <span class="brand-text">
          <span class="brand-han">{{ $appName }}</span>
          <span class="brand-viet">Học tiếng Trung</span>
        </span>
      </a>
      <a class="btn btn-primary btn-sm" href="{{ $ctaUrl ?? url('/') }}">{{ $ctaLabel ?? 'Học ngay' }}</a>
    </div>
  </header>
  <main id="landingMain" class="landing-main container">
    @yield('content')
  </main>
  <footer class="site-footer landing-footer">
    <div class="footer-inner">
      <p class="footer-meta">© {{ date('Y') }} HanViet Learn · <a href="{{ url('/privacy') }}">Chính sách quyền riêng tư</a></p>
    </div>
  </footer>
</body>
</html>
