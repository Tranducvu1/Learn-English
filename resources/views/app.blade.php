<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="hanviet-api" content="{{ url('/api') }}" />
  <title>{{ $seoTitle ?? ($appName . ' — Luyện thi HSK & Học Tiếng Trung Online') }}</title>
  @include('partials.seo-meta')
  @if($adsVerification)
  <meta name="google-adsense-account" content="{{ $adsVerification }}">
  @endif
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90' fill='%232563eb'>中</text></svg>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+SC:wght@400;500;600;700&family=Noto+Serif+SC:wght@500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ $assetVersion }}" />
  @include('partials.analytics')
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebApplication',
    'name' => config('hanviet.name'),
    'description' => config('hanviet.description'),
    'url' => url('/'),
    'applicationCategory' => 'EducationalApplication',
    'operatingSystem' => 'Web',
    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
    'inLanguage' => 'vi',
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
  </script>
  @if($adsEnabled)
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsClientId }}" crossorigin="anonymous"></script>
  @endif
</head>
<body>
  <a class="skip-link" href="#appMain">Chuyển tới nội dung chính</a>
  <script>window.HANVIET_CONFIG = @json($hanvietConfig);</script>
  <div id="loading" class="loading-screen">
    <div class="loading-brand">汉越学堂</div>
    <div class="loading-bar"><div class="loading-bar-fill"></div></div>
    <p class="loading-text">Đang tải nội dung học...</p>
  </div>

  <header class="site-header">
    <div class="header-inner">
      <a href="#" class="brand" onclick="App.navigate('dashboard');return false">
        <span class="brand-mark">中</span>
        <span class="brand-text">
          <span class="brand-han">汉越学堂</span>
          <span class="brand-viet">Học tiếng Trung</span>
        </span>
      </a>
      <nav class="header-nav" id="headerNav" aria-label="Điều hướng chính">
        <a href="#" class="nav-item active" data-page="dashboard">Trang chủ</a>
        <a href="#" class="nav-item" data-page="lessons">Luyện HSK</a>
        <a href="#" class="nav-item" data-page="quiz">Luyện thi</a>
        <a href="#" class="nav-item" data-page="roadmap">🗺️ Lộ trình</a>
        <a href="#" class="nav-item" data-page="exam-tips">💡 Mẹo thi</a>
        <a href="#" class="nav-item nav-item--premium" data-page="premium">👑 Premium</a>
        <div class="nav-more-wrap">
          <button type="button" class="nav-item nav-more-btn" id="navMoreBtn">Thêm ▾</button>
          <div class="nav-more-menu hidden" id="navMoreMenu">
            <a href="#" data-page="vocabulary">📚 Từ vựng</a>
            <a href="#" data-page="flashcards">🃏 Flashcard</a>
            <a href="#" data-page="videos">🎬 Video</a>
            <a href="#" data-page="ai-tutor">🤖 AI Tutor</a>
            <a href="#" data-page="voice">🔊 Luyện giọng</a>
            <a href="#" data-page="journal">📊 Tiến độ</a>
            <a href="#" data-page="dictionary">📕 Từ điển</a>
          </div>
        </div>
      </nav>
      <div class="header-actions">
        <div class="streak-pill">🔥 <span id="streakCount">0</span> ngày</div>
        <span id="premiumBadge" class="pro-badge hidden">PRO</span>
        <button class="btn btn-outline btn-sm" id="loginBtn" type="button" onclick="App.showAuthModal('login')">Đăng nhập</button>
        <div class="user-pill hidden" id="userPill">
          <span class="user-pill-name" id="userName">—</span>
          <button class="btn btn-ghost btn-sm" type="button" onclick="App.logout()">Thoát</button>
        </div>
        <button class="icon-btn" id="themeToggle" title="Giao diện">🌙</button>
        <button class="btn btn-premium btn-sm" id="premiumHeaderBtn" type="button" onclick="App.navigate('premium')">👑 Premium</button>
        <button class="btn btn-primary btn-sm header-cta" onclick="App.navigate('lessons')">Học ngay</button>
        <button class="icon-btn menu-btn" id="menuToggle" aria-label="Menu">☰</button>
      </div>
    </div>
  </header>

  <main class="app-main" id="appMain">

    <!-- TRANG CHỦ -->
    <section class="page active" id="page-dashboard">
      <div class="hero">
        <div class="hero-bg"></div>
        <div class="hero-inner">
          <div class="hero-copy">
            <p class="hero-eyebrow"><span class="hero-dot"></span> 1,200+ từ · 214 bài học · Miễn phí</p>
            <h1>Học tiếng Trung & luyện thi <em>HSK</em> hiệu quả</h1>
            <p class="hero-desc">Bài học HSK có hội thoại, flashcard SRS thông minh, luyện đề thi thử và video bài giảng — thiết kế cho người Việt.</p>
            <div class="hero-actions">
              <button class="btn btn-primary" onclick="App.navigate('lessons')">Bắt đầu học HSK 1</button>
              <button class="btn btn-white" onclick="App.navigate('flashcards')">Ôn flashcard</button>
              <button class="btn btn-premium-outline" id="heroPremiumBtn" type="button" onclick="App.navigate('premium')">👑 Xem Premium</button>
            </div>
            <div class="hero-stats">
              <div class="hero-stat"><strong id="heroWords">0</strong><span>từ đã học</span></div>
              <div class="hero-stat"><strong id="heroLessons">0</strong><span>bài hoàn thành</span></div>
              <div class="hero-stat"><strong id="heroVocabTotal">1200+</strong><span>từ vựng HSK</span></div>
            </div>
          </div>
          <div class="hero-visual">
            <div class="hero-card-preview">
              <div class="hanzi-big">你好</div>
              <div class="pinyin-line">nǐ hǎo</div>
              <p class="hero-card-label">Xin chào — HSK 1</p>
              <button class="btn btn-primary btn-sm" style="width:100%;margin-top:20px" onclick="Voice.speak('你好')">🔊 Nghe phát âm</button>
            </div>
          </div>
        </div>
      </div>

      <div class="container">
        <div id="dashAuthBanner" class="hidden mb-3"></div>
        <div id="dashPremiumBanner" class="hidden mb-3"></div>
        <div id="dashDailyTip" class="dash-daily-tip hidden mb-3"></div>
        <div class="stats-row" id="dashStats"></div>

        <div class="section-head">
          <h2>Khám phá — học thông minh hơn</h2>
          <p>Lộ trình · Mẹo thi · AI RAG · Video VIP</p>
        </div>
        <div class="po-hub-grid mb-3" id="dashFeatureHub"></div>

        <div class="section-head">
          <h2>Chọn cấp độ HSK</h2>
          <p>Luyện tập theo từng cấp độ — giống format đề thi thật</p>
        </div>
        <div class="exam-grid mb-3" id="dashHsk"></div>

        @if($adsEnabled)
        @include('partials.adsense-unit', [
          'name' => 'banner',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['banner'] ?? '',
          'autoFormat' => $adsAutoAds && empty($adsSlots['banner']),
        ])
        @endif

        <div class="section-head">
          <h2>Học theo chủ đề</h2>
        </div>
        <div class="topic-pills" id="dashTopics"></div>
        <div class="flex gap-2 flex-wrap">
          <button class="btn btn-primary" onclick="App.navigate('lessons')">Xem tất cả bài học</button>
          <button class="btn btn-outline" onclick="App.navigate('quiz')">Làm bài kiểm tra</button>
          <button class="btn btn-outline" onclick="App.navigate('exam-tips')">💡 Mẹo điểm cao</button>
        </div>
      </div>
    </section>

    <!-- BÀI HỌC -->
    <section class="page" id="page-lessons">
      <div class="container">
        <div class="section-head page-header">
          <h2>📖 Luyện thi HSK & Bài học</h2>
          <p>Hệ thống bài theo cấp độ và chủ đề thực tế</p>
        </div>
        <div class="lesson-filter-bar" role="search" aria-label="Lọc bài học">
          <div class="filter-group">
            <span class="filter-label">Cấp độ HSK</span>
            <div class="topic-pills" id="lessonHskPills"></div>
          </div>
          <div class="filter-group">
            <span class="filter-label">Chủ đề</span>
            <div class="topic-pills topic-pills-scroll" id="lessonTopics"></div>
          </div>
        </div>
        <div class="grid-2 lesson-list-grid" id="lessonList" role="list" aria-label="Danh sách bài học"></div>
        @if($adsEnabled)
        @include('partials.adsense-unit', [
          'name' => 'lessons',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['lessons'] ?? '',
          'autoFormat' => $adsAutoAds && empty($adsSlots['lessons']),
        ])
        @endif
        <div id="lessonDetail" class="hidden"></div>
      </div>
    </section>

    <!-- TỪ VỰNG -->
    <section class="page" id="page-vocabulary">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>📚 Kho từ vựng HSK</h2>
          <p id="vocabCountLabel">Đang tải...</p>
        </div>
        <div class="flex gap-2 mb-2 flex-wrap">
          <div class="search-box" style="flex:1;min-width:200px;margin:0">
            <span class="search-icon">🔍</span>
            <input type="search" class="search-input" id="vocabSearch" placeholder="Tìm Hán tự, pinyin, nghĩa..." />
          </div>
          <select id="vocabHskFilter" class="search-input" style="width:auto;padding:12px 16px">
            <option value="">Tất cả HSK</option>
            <option value="1">HSK 1</option>
            <option value="2">HSK 2</option>
            <option value="3">HSK 3</option>
            <option value="4">HSK 4</option>
            <option value="5">HSK 5</option>
            <option value="6">HSK 6</option>
          </select>
        </div>
        <div class="topic-pills topic-pills-scroll mb-2" id="vocabTopicPills"></div>
        @if($adsEnabled)
        @include('partials.adsense-unit', [
          'name' => 'vocab',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['vocab'] ?? '',
          'autoFormat' => $adsAutoAds && empty($adsSlots['vocab']),
        ])
        @endif
        <div class="vocab-list-panel" id="vocabList"></div>
        <div class="flex gap-2 mt-2">
          <button class="btn btn-outline btn-sm" id="vocabPrev">← Trước</button>
          <span class="text-muted text-sm" id="vocabPageInfo"></span>
          <button class="btn btn-outline btn-sm" id="vocabNext">Sau →</button>
        </div>
      </div>
    </section>

    <!-- LUYỆN GIỌNG -->
    <section class="page" id="page-voice">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>🔊 Luyện nghe & phát âm</h2>
          <p>Nghe Hán tự · Pinyin chậm/nhanh · Luyện nghe liên tục</p>
        </div>
        <div class="grid-2">
          <div class="card">
            <h3 class="card-title mb-2" id="voiceWordHanzi">你好</h3>
            <p class="pinyin" id="voiceWordPinyin" style="font-size:1.5rem">nǐ hǎo</p>
            <p id="voiceWordVi" class="mb-2">Xin chào</p>
            <div class="flex gap-1 flex-wrap">
              <button class="btn btn-primary" onclick="App.voicePlayCurrent('hanzi')">🔊 Đọc Hán tự</button>
              <button class="btn btn-outline" onclick="App.voicePlayCurrent('slow')">🐢 Chậm</button>
              <button class="btn btn-outline" onclick="App.voicePlayCurrent('normal')">▶ Bình thường</button>
            </div>
            <div class="flex gap-1 mt-2">
              <button class="btn btn-ghost btn-sm" onclick="App.voicePrev()">← Từ trước</button>
              <button class="btn btn-ghost btn-sm" onclick="App.voiceNext()">Từ sau →</button>
            </div>
          </div>
          <div class="card">
            <h3 class="card-title mb-2">Chế độ luyện nghe</h3>
            <p class="text-muted text-sm mb-2">Giọng Youdao (chuẩn) · Chỉ đọc <strong>Hán tự</strong>, không đọc pinyin</p>
            <label class="text-sm text-muted">Nguồn phát âm</label>
            <select id="ttsEngine" class="search-input mb-2" onchange="Voice.setEngine(this.value)">
              <option value="youdao">Youdao — Tiếng Trung chuẩn (khuyên dùng)</option>
              <option value="browser">Giọng trình duyệt (macOS/Windows)</option>
            </select>
            <p class="text-muted text-sm mb-2">Tự động đọc lần lượt từ trong danh sách (HSK 1 mặc định)</p>
            <select id="voiceHskSelect" class="search-input mb-2">
              <option value="1">HSK 1</option>
              <option value="2">HSK 2</option>
              <option value="3">HSK 3</option>
              <option value="4">HSK 4</option>
            </select>
            <button class="btn btn-primary" style="width:100%" onclick="App.voicePlayAll()">▶ Phát tất cả</button>
            <button class="btn btn-outline mt-2" style="width:100%" onclick="Voice.stop()">⏹ Dừng</button>
          </div>
        </div>
      </div>
    </section>

    <!-- FLASHCARD -->
    <section class="page" id="page-flashcards">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>🃏 Flashcard — Spaced Repetition</h2>
          <p>Nhấn thẻ để lật · Đánh giá mức nhớ</p>
        </div>
        <div class="flex gap-1 mb-3" id="srsStats"></div>
        @if($adsEnabled)
        @include('partials.adsense-unit', [
          'name' => 'flashcards',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['flashcards'] ?? '',
          'autoFormat' => $adsAutoAds && empty($adsSlots['flashcards']),
        ])
        @endif
        <div id="flashcardArea"></div>
      </div>
    </section>

    <!-- KỸ NĂNG -->
    <section class="page" id="page-skills">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>🎯 Luyện 4 kỹ năng</h2>
        </div>
        <div class="tabs">
          <button class="tab active" data-skill="listen" onclick="App.setSkill('listen')">👂 Nghe</button>
          <button class="tab" data-skill="read" onclick="App.setSkill('read')">📖 Đọc</button>
          <button class="tab" data-skill="write" onclick="App.setSkill('write')">✍️ Viết</button>
          <button class="tab" data-skill="speak" onclick="App.setSkill('speak')">🗣️ Nói</button>
        </div>
        <div class="card" id="skillContent">
          <h3 class="card-title mb-2" id="skillTitle">👂 Nghe</h3>
          <div id="skillBody"></div>
        </div>
      </div>
    </section>

    <!-- QUIZ -->
    <section class="page" id="page-quiz">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>📝 Luyện thi HSK 1–6</h2>
          <p>Mô phỏng đề thi thật · Giải thích chi tiết · Estudyme-style</p>
        </div>
        <div class="exam-stat-bar" id="quizStats"></div>
        <div class="hsk-exam-tabs" id="quizHskTabs"></div>
        @if($adsEnabled)
        @include('partials.adsense-unit', [
          'name' => 'quiz',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['quiz'] ?? '',
          'autoFormat' => $adsAutoAds && empty($adsSlots['quiz']),
        ])
        @endif
        <div class="grid-2" id="quizList"></div>
        <div id="quizAdAfter" class="ad-slot-dynamic"></div>
        <div id="quizArea" class="hidden"></div>
      </div>
    </section>

    <!-- TỪ ĐIỂN -->
    <section class="page" id="page-dictionary">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>📕 Từ điển tích hợp</h2>
        </div>
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input type="search" class="search-input" id="dictSearch" placeholder="Tra Hán tự, pinyin hoặc nghĩa tiếng Việt..." />
        </div>
        <div class="card" style="padding:0;overflow:hidden" id="dictResults"></div>
      </div>
    </section>

    <!-- VIDEO -->
    <section class="page" id="page-videos">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>🎬 Video bài giảng HSK</h2>
          <p>Playlist: <a href="https://www.youtube.com/playlist?list=PLWXyZU_NJb_chvMZ13hgOPB3Vcz7xhW3q" target="_blank" rel="noopener" style="color:var(--primary)">YouTube HSK Course</a></p>
        </div>
        <div id="videoPlaylistEmbed" class="mb-3"></div>
        <div id="videoPlayer" class="hidden"></div>
        <div class="section-head mt-2"><h3>🎬 Video miễn phí</h3></div>
        <div class="video-grid" id="videoGrid"></div>
        <div class="section-head mt-3" id="vipVideoHead"><h3>👑 Video VIP <span class="pro-badge">PRO</span></h3><p class="text-muted text-sm">Mở khóa Premium để xem toàn bộ</p></div>
        <div class="video-grid" id="vipVideoGrid"></div>
      </div>
    </section>

    <!-- NHẬT KÝ -->
    <section class="page" id="page-journal">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>📊 Tiến độ học tập</h2>
        </div>
        <div id="journalStats"></div>
        <h3 class="card-title mt-2 mb-2">Tiến độ theo HSK</h3>
        <div id="journalHsk"></div>
        <h3 class="card-title mt-2 mb-2">Tiến độ theo chủ đề</h3>
        <div id="journalTopics"></div>
        <h3 class="card-title mt-2 mb-2">Danh sách bài học</h3>
        <div id="journalLessonList" class="journal-lesson-list mb-2"></div>
        <h3 class="card-title mb-2">Nhật ký gần đây</h3>
        <div class="card" id="journalStudyLog"></div>
      </div>
    </section>

    <!-- LỘ TRÌNH -->
    <section class="page" id="page-roadmap">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>🗺️ Lộ trình học HSK</h2>
          <p id="roadmapSubtitle">12 tuần từ zero → sẵn sàng thi</p>
        </div>
        <div id="roadmapPhases"></div>
        <div id="roadmapPremiumCta" class="card premium-cta-card mt-3"></div>
      </div>
    </section>

    <!-- MẸO THI -->
    <section class="page" id="page-exam-tips">
      <div class="container">
        <div class="section-head page-header" style="text-align:left">
          <h2>💡 Mẹo đạt điểm cao HSK</h2>
          <p>Chiến lược theo từng cấp — từ người luyện thi thực chiến</p>
        </div>
        <div class="hsk-exam-tabs mb-3" id="examTipsTabs"></div>
        <div id="examTipsGeneral" class="feature-grid mb-3"></div>
        <div id="examTipsHighScore" class="mb-3"></div>
        <div id="examTipsLevel"></div>
        <div id="examTipsSkills" class="grid-2 mt-3"></div>
      </div>
    </section>

    <!-- PREMIUM -->
    <section class="page page--no-ads" id="page-premium">
      <div class="container">
        <div class="premium-banner">
          <h2 style="font-size:1.75rem;margin-bottom:8px">👑 Nâng cấp Premium</h2>
          <p style="opacity:.9">Không quảng cáo · AI RAG · Video VIP · Lộ trình AI</p>
        </div>
        <div class="grid-2 mb-3" id="pricingCards"></div>
        <div class="card mb-3" id="premiumCompare"></div>
        <div class="section-head"><h2>4 Tính năng Premium</h2></div>
        <div class="grid-2 mb-3" id="premiumFeatures"></div>
        <div class="flex gap-2 flex-wrap">
          <button class="btn btn-outline" onclick="App.navigate('ai-tutor')">🤖 AI Tutor</button>
          <button class="btn btn-outline" onclick="App.navigate('pronunciation')">🎙️ Phát âm Pro</button>
          <button class="btn btn-outline" onclick="App.navigate('personalized')">📈 Lộ trình AI</button>
        </div>
      </div>
    </section>

    <!-- AI TUTOR -->
    <section class="page page--no-ads" id="page-ai-tutor">
      <div class="container">
        <div class="section-head page-header" style="text-align:left"><h2>🤖 AI Chinese Tutor <span class="tag tag-pro">RAG</span></h2><p class="text-muted">Tra từ vựng & hội thoại trong app khi trả lời</p></div>
        <div class="premium-lock hidden" id="aiTutorGate">
          <div style="font-size:3rem">🔒</div>
          <h3 class="mb-2">Tính năng Premium</h3>
          <p class="text-muted mb-3">Chat AI tiếng Trung, sửa ngữ pháp, role-play</p>
          <button class="btn btn-primary" data-upgrade>Nâng cấp ngay</button>
        </div>
        <div id="aiTutorContent">
          <div class="ai-tutor-toolbar mb-2">
            <select id="aiHskLevel" class="search-input" style="width:auto">
              <option value="hsk1">HSK 1</option>
              <option value="hsk2">HSK 2</option>
              <option value="hsk3">HSK 3</option>
              <option value="hsk4">HSK 4</option>
              <option value="hsk5">HSK 5</option>
              <option value="hsk6">HSK 6</option>
            </select>
            <select id="aiMode" class="search-input" style="width:auto">
              <option value="tutor">Gia sư</option>
              <option value="roleplay">Role-play</option>
            </select>
            <select id="aiScenario" class="search-input hidden" style="width:auto"></select>
          </div>
          <p class="text-sm text-muted mb-2" id="aiTutorStatus"></p>
          <div class="topic-pills mb-2" id="aiTopicPills">
            <span class="topic-pill" data-ai-prompt="你好！">Chào hỏi</span>
            <span class="topic-pill" data-ai-prompt="请解释一下「谢谢」">Giải thích từ</span>
            <span class="topic-pill" data-ai-prompt="我想练习面试。">Phỏng vấn</span>
            <span class="topic-pill" data-ai-prompt="xin chào tiếng Trung">Hỏi tiếng Việt</span>
          </div>
          <div class="chat-panel">
            <div class="chat-messages" id="chatBox">
              <div class="chat-bubble ai"><strong>你好！我是你的中文老师。</strong><br><span class="text-muted text-sm">Hỏi bằng tiếng Trung hoặc tiếng Việt — RAG tra 1.200 từ + hội thoại bài học.</span></div>
            </div>
            <div class="chat-input-bar">
              <input type="text" id="chatInput" placeholder="Tiếng Trung hoặc tiếng Việt (vd: xin chào, 你好)..." />
              <button class="btn btn-primary btn-sm" id="chatSend">Gửi</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- PHÁT ÂM -->
    <section class="page page--no-ads" id="page-pronunciation">
      <div class="container">
        <div class="section-head page-header" style="text-align:left"><h2>🎙️ Phát âm chuyên sâu</h2></div>
        <div class="premium-lock" id="pronGate">
          <div style="font-size:3rem">🔒</div>
          <h3 class="mb-2">AI chấm điểm phát âm</h3>
          <button class="btn btn-primary mt-2" data-upgrade>Mở khóa Premium</button>
        </div>
        <div id="pronContent" class="hidden grid-2">
          <div class="card">
            <h3 class="card-title">Shadowing</h3>
            <div class="hanzi hanzi-xl text-center">你好</div>
            <p class="pinyin text-center mb-2">nǐ hǎo</p>
            <button class="btn btn-primary" style="width:100%" onclick="App.playAudio('nǐ hǎo')">▶ Nghe mẫu</button>
            <button class="btn btn-outline mt-2" style="width:100%" onclick="App.toggleRecording()">🎙️ Ghi âm</button>
            <p class="text-sm text-muted text-center mt-2" id="recordStatus">Nhấn để ghi âm</p>
          </div>
          <div class="card">
            <h3 class="card-title">Điểm AI</h3>
            <div style="font-size:3rem;font-weight:800;color:var(--primary);text-align:center" id="pronScore">—</div>
            <p class="text-center text-muted" id="pronFeedback">Ghi âm để AI chấm điểm phát âm</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CÁ NHÂN HÓA -->
    <section class="page page--no-ads" id="page-personalized">
      <div class="container">
        <div class="section-head page-header" style="text-align:left"><h2>📈 Lộ trình cá nhân AI</h2></div>
        <div class="premium-lock" id="persGate">
          <div style="font-size:3rem">🔒</div>
          <button class="btn btn-primary mt-2" data-upgrade>Mở khóa Premium</button>
        </div>
        <div id="persContent" class="hidden" id="learningPathWrap">
          <div id="learningPath"></div>
        </div>
      </div>
    </section>

  </main>

  @if($adsEnabled)
  <div class="container ad-slot-footer-wrap">
    @include('partials.adsense-unit', [
      'name' => 'footer',
      'clientId' => $adsClientId,
      'slotId' => $adsSlots['footer'] ?? '',
      'autoFormat' => $adsAutoAds && empty($adsSlots['footer']),
    ])
  </div>
  @endif

  <footer class="site-footer">
    <div class="footer-inner">
      <div>
        <div class="footer-brand">汉越学堂</div>
        <p class="footer-tagline">Nền tảng luyện thi HSK cho người Việt</p>
      </div>
      <p class="footer-meta">© {{ date('Y') }} HanViet Learn ·
        <a href="/privacy">Chính sách quyền riêng tư</a> ·
        <a href="/hoc-tieng-trung">Học tiếng Trung</a> ·
        <a href="/luyen-thi-hsk">Luyện thi HSK</a> ·
        <a href="/tu-vung-hsk">Từ vựng</a> ·
        <a href="/hsk-1">HSK 1</a>
      </p>
    </div>
  </footer>

  <nav class="mobile-nav" id="mobileNav" aria-label="Điều hướng chính">
    <button type="button" class="mobile-nav-item active" data-page="dashboard"><span class="nav-icon">🏠</span>Trang chủ</button>
    <button type="button" class="mobile-nav-item" data-page="lessons"><span class="nav-icon">📖</span>Bài học</button>
    <button type="button" class="mobile-nav-item" data-page="quiz"><span class="nav-icon">📝</span>Đề thi</button>
    <button type="button" class="mobile-nav-item mobile-nav-item--premium" data-page="premium"><span class="nav-icon">👑</span>Premium</button>
    <button type="button" class="mobile-nav-item" data-action="mobile-more"><span class="nav-icon">⋯</span>Thêm</button>
  </nav>

  <div class="mobile-more-sheet hidden" id="mobileMoreSheet" aria-hidden="true">
    <div class="mobile-more-backdrop" onclick="App.closeMobileMore()"></div>
    <div class="mobile-more-panel" role="dialog" aria-label="Thêm tính năng">
      <h3 class="mobile-more-title">Khám phá thêm</h3>
      <div class="mobile-more-grid">
        <button type="button" class="mobile-more-item" data-page="roadmap"><span>🗺️</span>Lộ trình</button>
        <button type="button" class="mobile-more-item" data-page="exam-tips"><span>💡</span>Mẹo thi</button>
        <button type="button" class="mobile-more-item" data-page="videos"><span>🎬</span>Video VIP</button>
        <button type="button" class="mobile-more-item" data-page="ai-tutor"><span>🤖</span>AI Tutor</button>
        <button type="button" class="mobile-more-item" data-page="flashcards"><span>🃏</span>Flashcard</button>
        <button type="button" class="mobile-more-item" data-page="vocabulary"><span>📚</span>Từ vựng</button>
        <button type="button" class="mobile-more-item" data-page="dictionary"><span>📕</span>Từ điển</button>
        <button type="button" class="mobile-more-item" data-page="journal"><span>📊</span>Tiến độ</button>
      </div>
      <button type="button" class="btn btn-ghost btn-sm mobile-more-close" onclick="App.closeMobileMore()">Đóng</button>
    </div>
  </div>

  <div class="modal-backdrop hidden" id="authModal">
    <div class="modal-box modal-auth">
      <div class="auth-tabs">
        <button type="button" class="auth-tab active" id="authTabLogin" onclick="App.switchAuthTab('login')">Đăng nhập</button>
        <button type="button" class="auth-tab" id="authTabRegister" onclick="App.switchAuthTab('register')">Đăng ký</button>
      </div>
      <p class="text-muted text-sm mb-3" id="authSubtitle">Đồng bộ tiến độ học trên mọi thiết bị</p>
      <p class="auth-error hidden" id="authError"></p>

      <div class="auth-social" id="authGoogleWrap">
        <a href="/auth/google" class="btn btn-google" id="googleLoginBtn" type="button">
          <svg class="btn-google-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
          Đăng nhập bằng Google
        </a>
        <p class="auth-social-hint hidden text-sm text-muted" id="googleLoginHint">Google chưa cấu hình. Đặt GOOGLE_CLIENT_ID trên server.</p>
      </div>
      <div class="auth-divider"><span>hoặc email</span></div>

      <form id="loginForm" class="auth-form" onsubmit="App.handleLogin(event)">
        <label class="form-field">
          <span>Email</span>
          <input class="form-input" type="email" name="email" required autocomplete="email" placeholder="ban@email.com" />
        </label>
        <label class="form-field">
          <span>Mật khẩu</span>
          <input class="form-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
        </label>
        <button class="btn btn-primary" type="submit" id="loginSubmit">Đăng nhập</button>
      </form>

      <form id="registerForm" class="auth-form hidden" onsubmit="App.handleRegister(event)">
        <label class="form-field">
          <span>Họ tên</span>
          <input class="form-input" type="text" name="name" required autocomplete="name" placeholder="Nguyễn Văn A" />
        </label>
        <label class="form-field">
          <span>Email</span>
          <input class="form-input" type="email" name="email" required autocomplete="email" placeholder="ban@email.com" />
        </label>
        <label class="form-field">
          <span>Mật khẩu</span>
          <input class="form-input" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" />
        </label>
        <label class="form-field">
          <span>Nhập lại mật khẩu</span>
          <input class="form-input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" />
        </label>
        <label class="form-field">
          <span>Cấp HSK hiện tại</span>
          <select class="form-input" name="hsk_level">
            <option value="">Chưa rõ</option>
            <option value="1">HSK 1</option>
            <option value="2">HSK 2</option>
            <option value="3">HSK 3</option>
            <option value="4">HSK 4</option>
            <option value="5">HSK 5</option>
            <option value="6">HSK 6</option>
          </select>
        </label>
        <button class="btn btn-primary" type="submit" id="registerSubmit">Tạo tài khoản</button>
      </form>

      <button class="btn btn-ghost btn-sm mt-2" type="button" onclick="App.closeModal()">Đóng</button>
    </div>
  </div>

  <div class="modal-backdrop hidden" id="wordModal">
    <div class="modal-box word-modal-box" role="dialog" aria-modal="true" aria-labelledby="wordModalHanzi">
      <button class="word-modal-close" type="button" id="wordModalClose" aria-label="Đóng">×</button>
      <div class="word-modal-hanzi-wrap">
        <span class="word-modal-hanzi" id="wordModalHanzi">字</span>
      </div>
      <p class="pinyin word-modal-pinyin" id="wordModalPinyin"></p>
      <p class="word-modal-meaning" id="wordModalMeaning"></p>
      <p class="word-modal-secondary text-muted text-sm hidden" id="wordModalSecondary"></p>
      <span class="tag tag-hsk" id="wordModalTag"></span>
      <div class="word-modal-sentences hidden" id="wordModalSentences"></div>
      <p class="word-modal-example text-sm mt-2 hidden" id="wordModalExample"></p>
      <div class="flex gap-2 mt-3">
        <button class="btn btn-primary" type="button" id="wordModalPlay">🔊 Nghe lại</button>
        <button class="btn btn-outline" type="button" id="wordModalDone">Đóng</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop hidden" id="upgradeModal">
    <div class="modal-box modal-payment">
      <h3>👑 Mua Premium</h3>
      <p class="text-muted text-sm mb-3" id="paymentSubtitle">Chọn gói và phương thức thanh toán</p>
      <p class="auth-error hidden" id="paymentError"></p>

      <div class="payment-plan-tabs">
        <button type="button" class="payment-plan-tab active" data-plan="monthly" onclick="App.selectPaymentPlan('monthly')">Gói tháng</button>
        <button type="button" class="payment-plan-tab" data-plan="yearly" onclick="App.selectPaymentPlan('yearly')">Gói năm <span class="payment-badge">Tiết kiệm</span></button>
      </div>
      <div class="payment-price" id="paymentPrice">—</div>

      <div class="payment-methods">
        <button type="button" class="btn btn-primary payment-btn" onclick="App.purchasePremium('sandbox')">
          💳 Thanh toán Sandbox
        </button>
        <button type="button" class="btn btn-outline payment-btn" onclick="App.purchasePremium('momo')" id="payMomoBtn" disabled>
          Momo (sắp ra mắt)
        </button>
        <button type="button" class="btn btn-outline payment-btn" onclick="App.purchasePremium('vnpay')" id="payVnpayBtn" disabled>
          VNPay (sắp ra mắt)
        </button>
      </div>
      <p class="text-muted text-xs mt-2" id="paymentModeHint">Sandbox: kích hoạt Premium ngay để thử nghiệm.</p>

      <div class="payment-footer">
        <button class="btn btn-ghost btn-sm" type="button" onclick="App.demoPremium()">Dùng thử Demo miễn phí</button>
        <button class="btn btn-ghost btn-sm" type="button" onclick="App.closeModal()">Đóng</button>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/api.js') }}?v={{ $assetVersion }}"></script>
  <script src="{{ asset('js/storage.js') }}?v={{ $assetVersion }}"></script>
  <script src="{{ asset('js/srs.js') }}?v={{ $assetVersion }}"></script>
  <script src="{{ asset('js/voice.js') }}?v={{ $assetVersion }}"></script>
  <script src="{{ asset('js/app.js') }}?v={{ $assetVersion }}"></script>
  @if($adsEnabled)
  <script>
    (function () {
      if (document.documentElement.classList.contains('no-ads')) return;
      @if($adsAutoAds)
      try {
        (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: '{{ $adsClientId }}',
          enable_page_level_ads: true
        });
      } catch (e) {}
      @endif
    })();
  </script>
  @endif
</body>
</html>
