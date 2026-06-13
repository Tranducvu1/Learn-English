<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="{{ config('hanviet.description') }}" />
  <meta name="hanviet-api" content="{{ url('/api') }}" />
  @if($adsVerification)
  <meta name="google-adsense-account" content="{{ $adsVerification }}">
  @endif
  <title>{{ $appName }} — Luyện thi HSK & Học Tiếng Trung Online</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90' fill='%232563eb'>中</text></svg>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+SC:wght@400;500;600;700&family=Noto+Serif+SC:wght@500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ $assetVersion }}" />
  @if($adsEnabled)
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsClientId }}" crossorigin="anonymous"></script>
  @endif
</head>
<body>
  <script>window.HANVIET_CONFIG = @json($hanvietConfig);</script>
  <script>
    try {
      const s = JSON.parse(localStorage.getItem('hanviet_state') || '{}');
      if (s.isPremium) document.documentElement.classList.add('no-ads');
    } catch (e) {}
  </script>
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
      <nav class="header-nav" id="headerNav">
        <a href="#" class="nav-item active" data-page="dashboard">Trang chủ</a>
        <a href="#" class="nav-item" data-page="lessons">Luyện HSK</a>
        <a href="#" class="nav-item" data-page="vocabulary">Từ vựng</a>
        <a href="#" class="nav-item" data-page="quiz">Luyện thi</a>
        <a href="#" class="nav-item" data-page="flashcards">Flashcard</a>
        <div class="nav-more-wrap">
          <button type="button" class="nav-item nav-more-btn" id="navMoreBtn">Thêm ▾</button>
          <div class="nav-more-menu hidden" id="navMoreMenu">
            <a href="#" data-page="voice">🔊 Luyện giọng</a>
            <a href="#" data-page="journal">📊 Tiến độ</a>
            <a href="#" data-page="dictionary">📕 Từ điển</a>
            <a href="#" data-page="videos">🎬 Video</a>
            <a href="#" data-page="roadmap">🗺️ Lộ trình</a>
            <a href="#" data-page="exam-tips">💡 Mẹo thi</a>
            <a href="#" data-page="premium">👑 Premium</a>
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
        <div class="stats-row" id="dashStats"></div>

        <div class="section-head">
          <h2>Chọn cấp độ HSK</h2>
          <p>Luyện tập theo từng cấp độ — giống format đề thi thật</p>
        </div>
        <div class="exam-grid mb-3" id="dashHsk"></div>

        @if($adsEnabled && ($adsSlots['banner'] || !$adsAutoAds))
        @include('partials.adsense-unit', [
          'name' => 'banner',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['banner'] ?? '',
          'autoFormat' => empty($adsSlots['banner']),
        ])
        @endif

        <div class="section-head">
          <h2>Chúng tôi có những gì bạn cần</h2>
          <p>Nội dung chất lượng · Mô phỏng đề thi · Theo dõi tiến độ cá nhân</p>
        </div>
        <div class="feature-grid mb-3">
          <div class="feature-card">
            <div class="feat-icon">📖</div>
            <h3>Bài học HSK</h3>
            <p>Hội thoại, từ vựng pinyin + tiếng Việt</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">🃏</div>
            <h3>Flashcard SRS</h3>
            <p>Ôn tập thông minh spaced repetition</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">📝</div>
            <h3>Luyện đề thi thử</h3>
            <p>Quiz có giải thích chi tiết</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">🎬</div>
            <h3>Video bài giảng</h3>
            <p>Playlist HSK YouTube tích hợp</p>
          </div>
        </div>

        <div class="section-head">
          <h2>Học theo chủ đề</h2>
        </div>
        <div class="topic-pills" id="dashTopics"></div>
        <div class="flex gap-2">
          <button class="btn btn-primary" onclick="App.navigate('lessons')">Xem tất cả bài học</button>
          <button class="btn btn-outline" onclick="App.navigate('quiz')">Làm bài kiểm tra</button>
        </div>
      </div>
    </section>

    <!-- BÀI HỌC -->
    <section class="page" id="page-lessons">
      <div class="container">
        <div class="section-head page-header" style="text-align:left;margin-bottom:24px">
          <h2>📖 Luyện thi HSK & Bài học</h2>
          <p>Hệ thống bài theo cấp độ và chủ đề thực tế</p>
        </div>
        <p class="text-sm text-muted mb-1">Cấp độ HSK</p>
        <div class="topic-pills mb-2" id="lessonHskPills"></div>
        <p class="text-sm text-muted mb-1">Chủ đề</p>
        <div class="topic-pills topic-pills-scroll mb-2" id="lessonTopics"></div>
        <div class="grid-2" id="lessonList"></div>
        @if($adsEnabled && ($adsSlots['lessons'] || !$adsAutoAds))
        @include('partials.adsense-unit', [
          'name' => 'lessons',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['lessons'] ?? '',
          'autoFormat' => empty($adsSlots['lessons']),
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
        @if($adsEnabled && ($adsSlots['vocab'] || !$adsAutoAds))
        @include('partials.adsense-unit', [
          'name' => 'vocab',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['vocab'] ?? '',
          'autoFormat' => empty($adsSlots['vocab']),
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
        @if($adsEnabled && ($adsSlots['flashcards'] || !$adsAutoAds))
        @include('partials.adsense-unit', [
          'name' => 'flashcards',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['flashcards'] ?? '',
          'autoFormat' => empty($adsSlots['flashcards']),
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
        @if($adsEnabled && !empty($adsSlots['quiz']))
        @include('partials.adsense-unit', [
          'name' => 'quiz',
          'clientId' => $adsClientId,
          'slotId' => $adsSlots['quiz'],
          'autoFormat' => false,
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
          <div class="topic-pills mb-2" id="aiTopicPills">
            <span class="topic-pill" data-ai-prompt="你好！">Chào hỏi</span>
            <span class="topic-pill" data-ai-prompt="请解释一下「谢谢」">Giải thích từ</span>
            <span class="topic-pill" data-ai-prompt="我想练习面试。">Phỏng vấn</span>
          </div>
          <div class="chat-panel">
            <div class="chat-messages" id="chatBox">
              <div class="chat-bubble ai"><strong>你好！我是你的中文老师。</strong><br><span class="text-muted text-sm">Xin chào! Hãy chat bằng tiếng Trung.</span></div>
            </div>
            <div class="chat-input-bar">
              <input type="text" id="chatInput" placeholder="Nhập tiếng Trung..." />
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
            <h3 class="card-title">Điểm AI (demo)</h3>
            <div style="font-size:3rem;font-weight:800;color:var(--primary);text-align:center">87</div>
            <p class="text-center text-muted">/ 100 điểm phát âm</p>
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

  @if($adsEnabled && ($adsSlots['footer'] || !$adsAutoAds))
  <div class="container ad-slot-footer-wrap">
    @include('partials.adsense-unit', [
      'name' => 'footer',
      'clientId' => $adsClientId,
      'slotId' => $adsSlots['footer'] ?? '',
      'autoFormat' => empty($adsSlots['footer']),
    ])
  </div>
  @endif

  <footer class="site-footer">
    <div class="footer-inner">
      <div>
        <div class="footer-brand">汉越学堂</div>
        <p class="footer-tagline">Nền tảng luyện thi HSK cho người Việt</p>
      </div>
      <p class="footer-meta">© {{ date('Y') }} HanViet Learn · <a href="/privacy">Chính sách quyền riêng tư</a></p>
    </div>
  </footer>

  <nav class="mobile-nav" id="mobileNav" aria-label="Điều hướng chính">
    <button type="button" class="mobile-nav-item active" data-page="dashboard"><span class="nav-icon">🏠</span>Trang chủ</button>
    <button type="button" class="mobile-nav-item" data-page="lessons"><span class="nav-icon">📖</span>Bài học</button>
    <button type="button" class="mobile-nav-item" data-page="flashcards"><span class="nav-icon">🃏</span>Thẻ</button>
    <button type="button" class="mobile-nav-item" data-page="quiz"><span class="nav-icon">📝</span>Đề thi</button>
    <button type="button" class="mobile-nav-item" data-page="journal"><span class="nav-icon">📊</span>Tiến độ</button>
  </nav>

  <div class="modal-backdrop hidden" id="authModal">
    <div class="modal-box modal-auth">
      <div class="auth-tabs">
        <button type="button" class="auth-tab active" id="authTabLogin" onclick="App.switchAuthTab('login')">Đăng nhập</button>
        <button type="button" class="auth-tab" id="authTabRegister" onclick="App.switchAuthTab('register')">Đăng ký</button>
      </div>
      <p class="text-muted text-sm mb-3" id="authSubtitle">Đồng bộ tiến độ học trên mọi thiết bị</p>
      <p class="auth-error hidden" id="authError"></p>

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
    <div class="modal-box">
      <h3>👑 Nâng cấp Premium</h3>
      <p class="text-muted mb-3">AI Tutor · Phát âm Pro · Lộ trình AI · Video độc quyền</p>
      <div class="flex gap-2">
        <button class="btn btn-primary" onclick="App.demoPremium()">Dùng thử Demo</button>
        <button class="btn btn-ghost" onclick="App.closeModal()">Đóng</button>
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
      document.querySelectorAll('.adsbygoogle').forEach(function (el) {
        try { (adsbygoogle = window.adsbygoogle || []).push({}); } catch (e) {}
      });
    })();
  </script>
  @endif
</body>
</html>
