@extends('layouts.landing')

@section('content')
  <div class="landing-hero">
    <p class="landing-eyebrow">汉越学堂 · Miễn phí cho người Việt</p>
    <h1>{{ $h1 }}</h1>
    <p class="landing-intro">{{ $intro }}</p>
    <div class="landing-cta-row">
      <a class="btn btn-primary btn-lg landing-cta" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
      <a class="btn btn-outline btn-lg" href="{{ url('/') }}">Về trang chủ app</a>
    </div>
  </div>

  @if(!empty($features))
  <section class="landing-section" aria-labelledby="landing-features">
    <h2 id="landing-features">Tại sao chọn {{ $appName }}?</h2>
    <div class="landing-feature-grid">
      @foreach($features as $f)
      <div class="landing-feature card">
        <div class="landing-feature-icon">{{ $f['icon'] }}</div>
        <h3>{{ $f['title'] }}</h3>
        <p>{{ $f['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </section>
  @endif

  <section class="landing-section landing-links" aria-label="Liên kết nội bộ">
    <h2>Khám phá thêm</h2>
    <div class="landing-link-row">
      <a href="{{ url('/hoc-tieng-trung') }}">Học tiếng Trung</a>
      <a href="{{ url('/luyen-thi-hsk') }}">Luyện thi HSK</a>
      <a href="{{ url('/tu-vung-hsk') }}">Từ vựng HSK</a>
      @foreach(range(1, 6) as $lv)
      <a href="{{ url("/hsk-{$lv}") }}">HSK {{ $lv }}</a>
      @endforeach
    </div>
  </section>
@endsection
