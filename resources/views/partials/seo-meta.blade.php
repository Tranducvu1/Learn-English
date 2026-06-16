@php
  $seoTitle = $seoTitle ?? ($appName . ' — Luyện thi HSK & Học Tiếng Trung Online');
  $seoDescription = $seoDescription ?? config('hanviet.description');
  $seoCanonical = $seoCanonical ?? url('/');
  $seoOgImage = $seoOgImage ?? config('hanviet.seo.og_image_url');
  $seoOgType = $seoOgType ?? 'website';
@endphp
<meta name="description" content="{{ $seoDescription }}" />
<link rel="canonical" href="{{ $seoCanonical }}" />
<meta property="og:locale" content="vi_VN" />
<meta property="og:type" content="{{ $seoOgType }}" />
<meta property="og:site_name" content="{{ $appName ?? config('hanviet.name') }}" />
<meta property="og:title" content="{{ $seoTitle }}" />
<meta property="og:description" content="{{ $seoDescription }}" />
<meta property="og:url" content="{{ $seoCanonical }}" />
@if($seoOgImage)
<meta property="og:image" content="{{ $seoOgImage }}" />
<meta property="og:image:alt" content="{{ $appName ?? config('hanviet.name') }} — Học tiếng Trung & luyện thi HSK" />
@endif
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $seoTitle }}" />
<meta name="twitter:description" content="{{ $seoDescription }}" />
@if($seoOgImage)
<meta name="twitter:image" content="{{ $seoOgImage }}" />
@endif
