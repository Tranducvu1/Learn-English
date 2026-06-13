/**
 * 汉越学堂 — Web học tiếng Trung
 */
const App = {
  data: {},
  state: Storage.get(),
  user: null,
  currentPage: 'dashboard',
  flashcardIndex: 0,
  dueCards: [],
  quizState: null,
  selectedLesson: null,
  selectedVideo: null,
  activeTopic: null,
  activeHsk: null,
  activeVocabTopic: null,
  activeQuizHsk: null,
  activeExamTipsHsk: 'hsk1',
  _chatSessionId: null,
  _aiMode: 'tutor',
  _aiScenario: null,

  NO_ADS_PAGES: ['premium', 'ai-tutor', 'pronunciation', 'personalized'],

  async init() {
    try {
      await this.loadData();
      await this.restoreSession();
      this.applyTheme();
      this.bindNav();
      this.bindGlobal();
      this.initAiTutorUi();
      Storage.updateStudyStreak();
      this.state = Storage.get();
      if (this.data.lessons?.levels?.length) {
        this.state = Storage.recalcHskProgress(this.data.lessons.levels);
      }
      this.renderAll();
      this.setSkill('listen');
    } catch (err) {
      console.error(err);
      const hint = HanVietAPI?.requiresBackend?.()
        ? '<p class="text-muted text-sm mt-2">Chạy Laravel: <code>npm run start</code></p>'
        : App.isFileProtocol()
          ? '<p class="text-muted text-sm mt-2">Mở file trực tiếp cần có <code>js/data-bundle.js</code>. Chạy: <code>npm run data:build</code></p>'
          : '<p class="text-muted text-sm mt-2">Chạy Laravel: <code>npm run setup</code> rồi <code>npm run start</code></p>';
      document.getElementById('loading').innerHTML =
        `<p style="color:#ef4444">Lỗi tải dữ liệu: ${err.message}</p>${hint}`;
      return;
    }
    document.getElementById('loading')?.classList.add('hidden');
  },

  isFileProtocol() {
    return window.location.protocol === 'file:';
  },

  async loadData() {
    // Bắt buộc Laravel API — không fallback static
    if (window.HanVietAPI?.requiresBackend?.()) {
      if (!HanVietAPI.enabled()) {
        throw new Error('Backend chưa cấu hình. Kiểm tra meta hanviet-api.');
      }
      await HanVietAPI.health();
      const bundle = await HanVietAPI.loadContentBundle();
      if (!bundle.vocabulary?.words?.length) {
        throw new Error('Backend trả dữ liệu rỗng. Chạy: php artisan db:seed');
      }
      Object.assign(this.data, bundle);
      this.mergeLessonVocabSupplement();
      this._apiOnline = true;

      if (HanVietAPI.token) {
        try {
          const remote = await HanVietAPI.fetchProgress();
          this.mergeRemoteProgress(remote);
        } catch (e) {
          console.warn('[API] progress sync skipped:', e.message);
        }
      }
      return;
    }

    // Legacy static (chỉ khi không có meta hanviet-api)
    if (window.APP_DATA) {
      Object.assign(this.data, window.APP_DATA);
    }

    const bundled = this.data.vocabulary?.words?.length >= 500 &&
      this.data.quizzes?.quizzes?.length > 0;

    if (bundled || this.isFileProtocol()) {
      if (!this.data.vocabulary?.words?.length) {
        throw new Error('Thiếu dữ liệu. Chạy: npm run data:build');
      }
      return;
    }

    const files = ['lessons', 'vocabulary', 'quizzes', 'dictionary', 'videos', 'premium'];
    await Promise.all(files.map(async (f) => {
      if (this.data[f] && (f !== 'vocabulary' || this.data.vocabulary?.words?.length >= 500)) return;
      try {
        const r = await fetch(`./data/${f}.json`);
        if (r.ok) this.data[f] = await r.json();
      } catch (e) {
        console.warn('Fetch optional:', f, e.message);
      }
    }));

    if (!this.data.vocabulary?.words?.length) {
      throw new Error('Không tải được từ vựng. Chạy: npm run data:build');
    }
    this.mergeLessonVocabSupplement();
  },

  mergeRemoteProgress(remote) {
    if (!remote) return;
    const local = Storage.get();
    const merged = {
      ...local,
      isPremium: remote.isPremium ?? local.isPremium,
      streak: Math.max(local.streak || 0, remote.streak || 0),
      lastStudyDate: remote.lastStudyDate || local.lastStudyDate,
      totalStudyMinutes: Math.max(local.totalStudyMinutes || 0, remote.totalStudyMinutes || 0),
      wordsLearned: Math.max(local.wordsLearned || 0, remote.wordsLearned || 0),
      completedLessons: [...new Set([...(local.completedLessons || []), ...(remote.completedLessons || [])])],
      lessonProgress: { ...(remote.lessonProgress || {}), ...(local.lessonProgress || {}) },
      hskProgress: { ...(remote.hskProgress || {}), ...(local.hskProgress || {}) },
      srsCards: { ...(remote.srsCards || {}), ...(local.srsCards || {}) },
      quizScores: { ...(remote.quizScores || {}), ...(local.quizScores || {}) },
      settings: { ...local.settings, ...(remote.settings || {}) },
      studyLog: [...(remote.studyLog || []), ...(local.studyLog || [])].slice(0, 40),
    };
    Storage.save(merged);
    this.state = merged;
  },

  async restoreSession() {
    if (!this._apiOnline || !HanVietAPI.token) {
      this.updateAuthBar();
      return;
    }
    try {
      const { user } = await HanVietAPI.me();
      this.user = user;
      this.applyUserToState(user);
    } catch (e) {
      console.warn('[API] session expired:', e.message);
      HanVietAPI.setToken('');
      this.user = null;
      this.updateAuthBar();
    }
  },

  applyUserToState(user) {
    if (!user) return;
    if (user.isPremium) Storage.setPremium(true);
    if (user.settings) {
      Object.entries(user.settings).forEach(([k, v]) => Storage.setSetting(k, v));
    }
    this.state = Storage.get();
    this.setPremiumLocal(!!user.isPremium || !!this.state.isPremium);
    this.updateAuthBar();
    this.updateTopBar();
    this.applyTheme();
  },

  updateAuthBar() {
    const loggedIn = !!this.user;
    document.getElementById('loginBtn')?.classList.toggle('hidden', loggedIn);
    document.getElementById('userPill')?.classList.toggle('hidden', !loggedIn);
    const nameEl = document.getElementById('userName');
    if (nameEl && loggedIn) {
      nameEl.textContent = this.user.name?.split(' ')[0] || this.user.email;
      nameEl.title = this.user.email || '';
    }
  },

  showAuthModal(mode = 'login', subtitle = '') {
    this.switchAuthTab(mode);
    const sub = document.getElementById('authSubtitle');
    if (sub) sub.textContent = subtitle || 'Đồng bộ tiến độ học trên mọi thiết bị';
    this.setAuthError('');
    document.getElementById('authModal')?.classList.remove('hidden');
  },

  switchAuthTab(mode) {
    const login = mode === 'login';
    document.getElementById('authTabLogin')?.classList.toggle('active', login);
    document.getElementById('authTabRegister')?.classList.toggle('active', !login);
    document.getElementById('loginForm')?.classList.toggle('hidden', !login);
    document.getElementById('registerForm')?.classList.toggle('hidden', login);
    this.setAuthError('');
  },

  setAuthError(msg) {
    const el = document.getElementById('authError');
    if (!el) return;
    if (msg) {
      el.textContent = msg;
      el.classList.remove('hidden');
    } else {
      el.textContent = '';
      el.classList.add('hidden');
    }
  },

  async handleLogin(e) {
    e.preventDefault();
    if (!this._apiOnline) return;
    const form = e.target;
    const btn = document.getElementById('loginSubmit');
    const email = form.email.value.trim();
    const password = form.password.value;
    btn.disabled = true;
    this.setAuthError('');
    try {
      const data = await HanVietAPI.login(email, password);
      this.user = data.user;
      this.applyUserToState(data.user);
      try {
        const remote = await HanVietAPI.fetchProgress();
        this.mergeRemoteProgress(remote);
      } catch (err) {
        console.warn('[API] progress fetch:', err.message);
      }
      await this.syncProgressToServer();
      this.closeModal();
      this.renderAll();
    } catch (err) {
      this.setAuthError(HanVietAPI.formatError(err));
    } finally {
      btn.disabled = false;
    }
  },

  async handleRegister(e) {
    e.preventDefault();
    if (!this._apiOnline) return;
    const form = e.target;
    const btn = document.getElementById('registerSubmit');
    const payload = {
      name: form.name.value.trim(),
      email: form.email.value.trim(),
      password: form.password.value,
      password_confirmation: form.password_confirmation.value,
    };
    const hsk = form.hsk_level?.value;
    if (hsk) payload.hsk_level = hsk;
    btn.disabled = true;
    this.setAuthError('');
    try {
      const data = await HanVietAPI.register(payload);
      this.user = data.user;
      this.applyUserToState(data.user);
      await this.syncProgressToServer();
      this.closeModal();
      this.renderAll();
    } catch (err) {
      this.setAuthError(HanVietAPI.formatError(err));
    } finally {
      btn.disabled = false;
    }
  },

  async logout() {
    if (this._apiOnline && HanVietAPI.token) {
      try {
        await HanVietAPI.logout();
      } catch (e) {
        console.warn('[API] logout:', e.message);
      }
    } else {
      HanVietAPI.setToken('');
    }
    this.user = null;
    this.updateAuthBar();
  },

  requireLogin(message) {
    if (!this._apiOnline) return true;
    if (HanVietAPI.token && this.user) return true;
    this.showAuthModal('login', message || 'Đăng nhập để tiếp tục');
    return false;
  },

  async syncProgressToServer() {
    if (!this._apiOnline || !HanVietAPI.token) return;
    try {
      await HanVietAPI.syncProgress(this.state);
    } catch (e) {
      console.warn('[API] sync failed:', e.message);
    }
  },

  mergeLessonVocabSupplement() {
    const extra = [
      { id: 'vx001', hanzi: '你好', pinyin: 'nǐ hǎo', vietnamese: 'Xin chào', hsk: 1, topic: 'giao-tiep',
        example: { hanzi: '你好吗？', pinyin: 'Nǐ hǎo ma?', vietnamese: 'Bạn khỏe không?' } },
      { id: 'vx002', hanzi: '认识', pinyin: 'rèn shi', vietnamese: 'Quen biết', hsk: 1, topic: 'giao-tiep',
        example: { hanzi: '很高兴认识你', pinyin: 'Hěn gāoxìng rènshi nǐ', vietnamese: 'Rất vui được gặp bạn' } },
      { id: 'vx003', hanzi: '高兴', pinyin: 'gāo xìng', vietnamese: 'Vui, hạnh phúc', hsk: 2, topic: 'giao-tiep',
        example: { hanzi: '很高兴', pinyin: 'hěn gāoxìng', vietnamese: 'Rất vui' } },
      { id: 'vx004', hanzi: '很', pinyin: 'hěn', vietnamese: 'Rất', hsk: 1, topic: 'co-ban',
        example: { hanzi: '很好', pinyin: 'hěn hǎo', vietnamese: 'Rất tốt' } },
      { id: 'vx005', hanzi: '这', pinyin: 'zhè', vietnamese: 'Đây, cái này', hsk: 1, topic: 'co-ban',
        example: { hanzi: '这个', pinyin: 'zhège', vietnamese: 'Cái này' } },
      { id: 'vx006', hanzi: '是', pinyin: 'shì', vietnamese: 'Là', hsk: 1, topic: 'co-ban',
        example: { hanzi: '我是学生', pinyin: 'Wǒ shì xuéshēng', vietnamese: 'Tôi là học sinh' } },
      { id: 'vx007', hanzi: '的', pinyin: 'de', vietnamese: 'Của (trợ từ)', hsk: 1, topic: 'co-ban',
        example: { hanzi: '我的', pinyin: 'wǒ de', vietnamese: 'Của tôi' } },
      { id: 'vx008', hanzi: '多少', pinyin: 'duō shao', vietnamese: 'Bao nhiêu', hsk: 1, topic: 'mua-sam',
        example: { hanzi: '多少钱', pinyin: 'Duōshao qián', vietnamese: 'Bao nhiêu tiền' } },
      { id: 'vx009', hanzi: '块', pinyin: 'kuài', vietnamese: 'Đồng (tiền)', hsk: 2, topic: 'mua-sam',
        example: { hanzi: '二十块', pinyin: 'Èrshí kuài', vietnamese: 'Hai mươi tệ' } },
      { id: 'vx010', hanzi: '想', pinyin: 'xiǎng', vietnamese: 'Muốn, nghĩ', hsk: 2, topic: 'du-lich',
        example: { hanzi: '我想订房', pinyin: 'Wǒ xiǎng dìng fáng', vietnamese: 'Tôi muốn đặt phòng' } }
    ];
    const words = this.data.vocabulary.words;
    const have = new Set(words.map(w => w.hanzi));
    extra.forEach(e => { if (!have.has(e.hanzi)) words.unshift(e); });
  },

  applyTheme() {
    const dark = this.state.settings?.darkMode;
    document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
    document.getElementById('themeToggle').textContent = dark ? '☀️' : '🌙';
  },

  bindNav() {
    const go = (page) => {
      this.navigate(page);
      document.getElementById('headerNav')?.classList.remove('open');
    };
    document.querySelectorAll('.nav-item').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        go(link.dataset.page);
      });
    });
    document.querySelectorAll('.mobile-nav-item').forEach(btn => {
      btn.addEventListener('click', () => go(btn.dataset.page));
    });
    document.getElementById('menuToggle')?.addEventListener('click', () => {
      document.getElementById('headerNav')?.classList.toggle('open');
    });

    document.getElementById('navMoreBtn')?.addEventListener('click', e => {
      e.stopPropagation();
      document.getElementById('navMoreMenu')?.classList.toggle('hidden');
    });
    document.getElementById('navMoreMenu')?.querySelectorAll('[data-page]').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        go(link.dataset.page);
        document.getElementById('navMoreMenu')?.classList.add('hidden');
      });
    });
    document.addEventListener('click', () => {
      document.getElementById('navMoreMenu')?.classList.add('hidden');
    });
  },

  bindGlobal() {
    document.getElementById('themeToggle')?.addEventListener('click', () => {
      const dark = !this.state.settings?.darkMode;
      Storage.setSetting('darkMode', dark);
      this.state = Storage.get();
      this.applyTheme();
    });

    document.getElementById('dictSearch')?.addEventListener('input', e => {
      this.renderDictionary(e.target.value);
    });

    document.getElementById('vocabSearch')?.addEventListener('input', () => {
      this.vocabPage = 0;
      this.renderVocabulary();
    });
    document.getElementById('vocabHskFilter')?.addEventListener('change', () => {
      this.vocabPage = 0;
      this.activeVocabTopic = null;
      this.renderVocabulary();
    });
    document.getElementById('vocabPrev')?.addEventListener('click', () => {
      if (this.vocabPage > 0) { this.vocabPage--; this.renderVocabulary(); }
    });
    document.getElementById('vocabNext')?.addEventListener('click', () => {
      this.vocabPage++;
      this.renderVocabulary();
    });
    document.getElementById('voiceHskSelect')?.addEventListener('change', () => this.initVoicePage());

    document.getElementById('chatSend')?.addEventListener('click', () => this.sendChat());
    document.getElementById('chatInput')?.addEventListener('keypress', e => {
      if (e.key === 'Enter') this.sendChat();
    });

    document.getElementById('recordBtn')?.addEventListener('click', () => this.toggleRecording());

    document.querySelectorAll('[data-upgrade]').forEach(btn => {
      btn.addEventListener('click', () => this.showUpgradeModal());
    });

    this.bindVocabInteractions();
    this.bindModals();
  },

  bindModals() {
    const wordModal = document.getElementById('wordModal');
    const close = () => this.closeWordModal();

    document.getElementById('wordModalClose')?.addEventListener('click', close);
    document.getElementById('wordModalDone')?.addEventListener('click', close);
    document.getElementById('wordModalPlay')?.addEventListener('click', () => {
      const id = wordModal?.dataset.wordId;
      if (id) this.playWord(id);
    });

    wordModal?.addEventListener('click', e => {
      if (e.target === wordModal) close();
    });

    const box = wordModal?.querySelector('.word-modal-box');
    box?.addEventListener('click', e => e.stopPropagation());

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && wordModal && !wordModal.classList.contains('hidden')) {
        close();
      }
    });
  },

  bindVocabInteractions() {
    if (document._vocabDelegated) return;
    document._vocabDelegated = true;

    document.addEventListener('click', e => {
      const row = e.target.closest('[data-word-id]');
      if (!row) return;
      const id = row.dataset.wordId;
      if (e.target.closest('.audio-btn') || e.target.closest('.vocab-card-play')) {
        e.stopPropagation();
        if (row.dataset.hanziOnly) Voice.speak(id);
        else this.playWord(id);
        return;
      }
      if (row.dataset.hanziOnly) {
        Voice.speak(id);
        this.toast(`🔊 ${id}`);
        return;
      }
      this.openWord(id);
    });

    document.addEventListener('keydown', e => {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      const row = e.target.closest('[data-word-id]');
      if (!row || e.target.closest('.audio-btn')) return;
      e.preventDefault();
      const id = row.dataset.wordId;
      if (row.dataset.hanziOnly) Voice.speak(id);
      else this.openWord(id);
    });
  },

  wordMeaning(w) {
    if (!w) return { primary: '', secondary: null, isEnglish: false };
    const vi = (w.vietnamese || '').trim();
    const en = (w.english || '').trim();
    const looksVi = /[àáảãạăằắẳẵặâầấẩẫậèéẻẽẹêềếểễệìíỉĩịòóỏõọôồốổỗộơờớởỡợùúủũụưừứửữựỳýỷỹỵđ]/i.test(vi)
      || /(không|của|một|người|được|trong|bạn|tôi|xin chào|cảm ơn)/i.test(vi);
    if (looksVi) {
      return { primary: vi, secondary: en && en !== vi ? en : null, isEnglish: false };
    }
    return { primary: en || vi, secondary: null, isEnglish: true };
  },

  sentencePreview(w) {
    const s = w.sentences?.[0];
    if (!s?.hanzi) return '';
    const vi = s.vietnamese && !/^Bạn biết/i.test(s.vietnamese) ? s.vietnamese : '';
    if (!vi) return '';
    return `<p class="vocab-card-example">${s.hanzi}<span>${vi}</span></p>`;
  },

  hanziSizeClass(hanzi) {
    const len = [...(hanzi || '')].length;
    if (len <= 1) return 'hz-len1';
    if (len === 2) return 'hz-len2';
    if (len === 3) return 'hz-len3';
    return 'hz-len4';
  },

  vocabCardMeta(w, m, tMeta, sc) {
    const hskChip = `<span class="chip chip-hsk">HSK ${w.hsk}</span>`;
    const topicChip = tMeta ? `<span class="chip chip-topic">${tMeta.icon}</span>` : '';
    const sentChip = sc ? `<span class="chip chip-sent">${sc} câu</span>` : '';
    const viHint = m.isEnglish ? `<span class="chip chip-vi">VI soon</span>` : '';
    return `${hskChip}${topicChip}${sentChip}${viHint}`;
  },

  toast(msg, type = 'info') {
    let el = document.getElementById('appToast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'appToast';
      el.className = 'app-toast';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.className = `app-toast app-toast--${type} app-toast--show`;
    clearTimeout(this._toastTimer);
    this._toastTimer = setTimeout(() => el.classList.remove('app-toast--show'), 2400);
  },

  showWordModal(w) {
    const m = this.wordMeaning(w);
    const topics = this.data.lessons?.topics || [];
    const tMeta = topics.find(t => t.id === w.topic);
    const ex = w.example;
    const modal = document.getElementById('wordModal');
    if (!modal) return;
    document.getElementById('wordModalHanzi').textContent = w.hanzi;
    document.getElementById('wordModalPinyin').textContent = w.pinyin || '';
    document.getElementById('wordModalMeaning').textContent = m.primary;
    const sec = document.getElementById('wordModalSecondary');
    if (sec) {
      if (m.secondary) {
        sec.textContent = `English: ${m.secondary}`;
        sec.classList.remove('hidden');
      } else if (m.isEnglish) {
        sec.textContent = 'Đang cập nhật nghĩa tiếng Việt';
        sec.classList.remove('hidden');
      } else {
        sec.classList.add('hidden');
        sec.textContent = '';
      }
    }
    const tag = document.getElementById('wordModalTag');
    if (tag) tag.textContent = `HSK ${w.hsk}${tMeta ? ` · ${tMeta.icon} ${tMeta.name}` : ''}`;
    const exEl = document.getElementById('wordModalExample');
    const sentEl = document.getElementById('wordModalSentences');
    const sentences = w.sentences || [];

    if (sentEl) {
      if (sentences.length) {
        sentEl.innerHTML = `<div class="word-modal-subtitle">Câu ví dụ từ bài học & đề thi (${sentences.length})</div>` +
          sentences.map(s => `
            <div class="sentence-card">
              <div class="s-hanzi">${s.hanzi}</div>
              ${s.pinyin ? `<div class="s-pinyin">${s.pinyin}</div>` : ''}
              ${s.vietnamese ? `<div class="s-vi">${s.vietnamese}</div>` : ''}
              <div class="s-src">📖 ${s.source || 'Bài học'}</div>
            </div>`).join('');
        sentEl.classList.remove('hidden');
      } else {
        sentEl.classList.add('hidden');
        sentEl.innerHTML = '';
      }
    }

    if (exEl) {
      if (ex?.hanzi && !sentences.length) {
        exEl.innerHTML = `<strong>Ví dụ:</strong> ${ex.hanzi}${ex.pinyin ? ` <span class="pinyin">(${ex.pinyin})</span>` : ''}${ex.vietnamese ? ` — ${ex.vietnamese}` : ''}`;
        exEl.classList.remove('hidden');
      } else {
        exEl.classList.add('hidden');
        exEl.innerHTML = '';
      }
    }
    modal.dataset.wordId = w.id;
    modal.classList.remove('hidden');
    document.body.classList.add('modal-open');
  },

  closeWordModal() {
    const modal = document.getElementById('wordModal');
    modal?.classList.add('hidden');
    document.body.classList.remove('modal-open');
    Voice.stop();
  },

  async openWord(id) {
    const w = this.resolveWord(id);
    if (!w) {
      this.toast('Không tìm thấy từ', 'error');
      return;
    }
    this.showWordModal(w);
    await this.playWord(w.id, { silent: true });
  },

  async playWord(id, options = {}) {
    const w = this.resolveWord(id);
    if (!w) {
      if (!options.silent) this.toast('Không tìm thấy từ', 'error');
      return;
    }
    const row = document.querySelector(`[data-word-id="${w.id}"]`);
    row?.classList.add('dict-item--playing');
    if (!options.silent) this.toast(`🔊 ${w.hanzi}`);
    try {
      await Voice.speakWord(w);
      Storage.markWordHeard?.(w.id);
    } catch (e) {
      if (!options.silent) this.toast('Không phát được âm thanh. Thử đổi nguồn TTS.', 'error');
    } finally {
      row?.classList.remove('dict-item--playing');
    }
  },

  navigate(page) {
    this.currentPage = page;
    this._currentPage = page;
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(`page-${page}`)?.classList.add('active');
    document.querySelectorAll('.nav-item').forEach(l => {
      l.classList.toggle('active', l.dataset.page === page);
    });
    document.querySelectorAll('.mobile-nav-item').forEach(l => {
      l.classList.toggle('active', l.dataset.page === page);
    });
    const hero = document.querySelector('#page-dashboard .hero');
    if (hero) hero.style.display = page === 'dashboard' ? '' : 'none';
    this.renderPage(page);
    this.updateAdsVisibility();
    window.scrollTo(0, 0);
  },

  renderAll() {
    this.vocabPage = 0;
    this.vocabPageSize = 50;
    this.voiceIndex = 0;
    this.renderDashboard();
    this.renderLessons();
    this.renderVocabulary();
    this.initVoicePage();
    this.renderFlashcards();
    this.renderSkills();
    this.renderQuiz();
    this.renderDictionary();
    this.renderVideos();
    this.renderRoadmap();
    this.renderExamTips();
    this.renderPremium();
    this.renderJournal();
    this.updateTopBar();
    this.updateAuthBar();
  },

  renderPage(page) {
    const map = {
      dashboard: () => this.renderDashboard(),
      lessons: () => this.renderLessons(),
      vocabulary: () => this.renderVocabulary(),
      voice: () => this.initVoicePage(),
      flashcards: () => this.renderFlashcards(),
      skills: () => this.renderSkills(),
      quiz: () => {
        if (this.quizInProgress && this.quizState?.quiz) {
          const listEl = document.getElementById('quizList');
          const areaEl = document.getElementById('quizArea');
          if (listEl) listEl.classList.add('hidden');
          if (areaEl) areaEl.classList.remove('hidden');
          this.showQuizQuestion();
        } else {
          this.renderQuiz();
        }
      },
      dictionary: () => this.renderDictionary(),
      videos: () => this.renderVideos(),
      roadmap: () => this.renderRoadmap(),
      'exam-tips': () => this.renderExamTips(),
      premium: () => this.renderPremium(),
      journal: () => this.renderJournal(),
      'ai-tutor': () => this.renderAiTutor(),
      pronunciation: () => this.renderPronunciation(),
      personalized: () => this.renderPersonalized()
    };
    map[page]?.();
  },

  updateTopBar() {
    this.state = Storage.get();
    document.getElementById('streakCount').textContent = this.state.streak || 0;
    const badge = document.getElementById('premiumBadge');
    const isPro = this.user?.isPremium || this.state.isPremium;
    if (badge) badge.classList.toggle('hidden', !isPro);
    document.getElementById('premiumHeaderBtn')?.classList.toggle('hidden', isPro);
    document.getElementById('heroPremiumBtn')?.classList.toggle('hidden', isPro);
    this.updateAdsVisibility();
  },

  updateAdsVisibility() {
    const hide = this.isPremium();
    document.documentElement.classList.toggle('no-ads', hide);
    document.querySelectorAll('.ad-slot, .ad-slot-footer-wrap, .ad-slot-dynamic').forEach(el => {
      el.classList.toggle('hidden', hide);
    });
    const onNoAdsPage = this.NO_ADS_PAGES.includes(this._currentPage);
    if (hide || onNoAdsPage) {
      document.querySelectorAll('.ad-slot, .ad-slot-footer-wrap').forEach(el => el.classList.add('hidden'));
    }
  },

  setPremiumLocal(isPro) {
    if (isPro) document.documentElement.classList.add('no-ads');
    else document.documentElement.classList.remove('no-ads');
    this.updateAdsVisibility();
  },

  isPremium() {
    return this.user?.isPremium || this.state.isPremium;
  },

  escAttr(s) {
    return String(s ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
  },

  vocabMap() {
    const map = {};
    (this.data.vocabulary?.words || []).forEach(w => {
      map[w.id] = w;
      map[w.hanzi] = w;
      const m = w.id.match(/^v0*(\d+)$/i);
      if (m) {
        map[`v${m[1]}`] = w;
        map['v' + String(parseInt(m[1], 10)).padStart(3, '0')] = w;
      }
    });
    return map;
  },

  resolveWord(idOrHanzi) {
    const map = this.vocabMap();
    if (map[idOrHanzi]) return map[idOrHanzi];
    const m = String(idOrHanzi).match(/^v(\d+)$/i);
    if (m) {
      const padded = 'v' + String(parseInt(m[1], 10)).padStart(4, '0');
      if (map[padded]) return map[padded];
    }
    return (this.data.vocabulary?.words || []).find(w => w.hanzi === idOrHanzi);
  },

  /* ===== Dashboard ===== */
  renderDashboard() {
    const s = this.state;
    const vocab = this.data.vocabulary?.words || [];
    const due = SRS.getDueCards(vocab, s.srsCards || {});
    const lessonCount = (this.data.lessons?.levels || []).reduce((n, l) => n + (l.lessons?.length || 0), 0);

    const totalVocab = vocab.length;
    const elWords = document.getElementById('heroWords');
    const elLessons = document.getElementById('heroLessons');
    const elVocabTotal = document.getElementById('heroVocabTotal');
    if (elWords) elWords.textContent = s.wordsLearned || 0;
    if (elLessons) elLessons.textContent = s.completedLessons?.length || 0;
    if (elVocabTotal) elVocabTotal.textContent = totalVocab + '+';

    const authBanner = document.getElementById('dashAuthBanner');
    if (authBanner) {
      if (this._apiOnline && !this.user) {
        authBanner.innerHTML = `
          <div class="auth-banner">
            <div>
              <strong>Đăng nhập để lưu tiến độ</strong>
              <p class="text-sm text-muted">Đồng bộ streak, bài học và flashcard — học trên mọi thiết bị.</p>
            </div>
            <button class="btn btn-primary btn-sm" type="button" onclick="App.showAuthModal('register')">Tạo tài khoản</button>
          </div>`;
        authBanner.classList.remove('hidden');
      } else {
        authBanner.classList.add('hidden');
        authBanner.innerHTML = '';
      }
    }

    const premBanner = document.getElementById('dashPremiumBanner');
    if (premBanner) {
      if (this.isPremium()) {
        premBanner.classList.add('hidden');
        premBanner.innerHTML = '';
      } else {
        premBanner.classList.remove('hidden');
        premBanner.innerHTML = `
          <div class="premium-promo-banner">
            <div class="premium-promo-copy">
              <span class="premium-promo-tag">👑 Premium</span>
              <strong>Không quảng cáo · AI RAG · Video VIP · Lộ trình AI</strong>
              <p class="text-sm text-muted">Dùng thử demo miễn phí sau khi đăng nhập</p>
            </div>
            <div class="flex gap-2 flex-wrap">
              <button class="btn btn-premium btn-sm" type="button" onclick="App.navigate('premium')">Xem gói Premium</button>
              <button class="btn btn-outline btn-sm" type="button" onclick="App.showUpgradeModal()">Dùng thử Demo</button>
            </div>
          </div>`;
      }
    }

    document.getElementById('dashStats').innerHTML = `
      <div class="stat-box"><div class="stat-icon">🔥</div><div><div class="stat-num">${s.streak}</div><div class="stat-label">Streak ngày</div></div></div>
      <div class="stat-box"><div class="stat-icon">📚</div><div><div class="stat-num">${s.wordsLearned || 0}</div><div class="stat-label">Từ đã học</div></div></div>
      <div class="stat-box"><div class="stat-icon">⏱️</div><div><div class="stat-num">${s.totalStudyMinutes || 0}</div><div class="stat-label">Phút học</div></div></div>
      <div class="stat-box"><div class="stat-icon">🃏</div><div><div class="stat-num">${due.length}</div><div class="stat-label">Thẻ cần ôn</div></div></div>
    `;

    const levels = this.data.lessons?.levels || [];
    const hskIcons = { hsk1: '1', hsk2: '2', hsk3: '3', hsk4: '4', hsk5: '5', hsk6: '6' };
    const hskNumerals = { hsk1: '一', hsk2: '二', hsk3: '三', hsk4: '四', hsk5: '五', hsk6: '六' };
    document.getElementById('dashHsk').innerHTML = levels.map(l => {
      const done = (s.completedLessons || []).filter(id => id.startsWith(l.id)).length;
      const total = l.lessons?.length || l.totalLessons || 1;
      const pct = Math.min(100, Math.round((done / total) * 100));
      return `
        <div class="exam-card exam-card--cn" style="--card-color:${l.color}" onclick="App.filterHsk('${l.id}')">
          <span class="exam-level-num">${hskNumerals[l.id] || ''}</span>
          <div class="exam-card-deco"></div>
          <div class="exam-icon" style="background:linear-gradient(135deg,${l.color},color-mix(in srgb,${l.color} 70%,#000))">HSK ${hskIcons[l.id] || '?'}</div>
          <h3>${l.name}</h3>
          <p>${l.description}</p>
          <div class="exam-meta"><span>${total} bài</span><span class="exam-pct">${pct}%</span></div>
          <div class="progress-track progress-track--cn"><div class="progress-fill" style="width:${pct}%;background:linear-gradient(90deg,${l.color},color-mix(in srgb,${l.color} 60%,#fbbf24))"></div></div>
        </div>`;
    }).join('');

    document.getElementById('dashTopics').innerHTML = (this.data.lessons?.topics || []).map(t =>
      `<span class="topic-pill" onclick="App.filterTopic('${t.id}')">${t.icon} ${t.name} (${t.lessonCount})</span>`
    ).join('');
  },

  filterTopic(topicId) {
    this.navigate('lessons');
    this.activeTopic = topicId;
    this.renderLessons();
  },

  filterHsk(levelId) {
    this.navigate('lessons');
    this.activeHsk = levelId;
    this.renderLessons();
  },

  clearLessonFilters() {
    this.activeTopic = null;
    this.activeHsk = null;
    this.renderLessons();
  },

  /* ===== Lessons ===== */
  renderLessons() {
    const levels = this.data.lessons?.levels || [];
    const topics = this.data.lessons?.topics || [];
    const hskIcons = { hsk1: '1', hsk2: '2', hsk3: '3', hsk4: '4', hsk5: '5', hsk6: '6' };

    const hskEl = document.getElementById('lessonHskPills');
    if (hskEl) {
      hskEl.innerHTML = [
        `<span class="topic-pill hsk-pill ${!this.activeHsk ? 'active' : ''}" onclick="App.activeHsk=null;App.renderLessons()">Tất cả HSK</span>`,
        ...levels.map(l => {
          const done = (this.state.completedLessons || []).filter(id => id.startsWith(l.id)).length;
          const total = l.lessons?.length || 0;
          return `<span class="topic-pill hsk-pill ${this.activeHsk === l.id ? 'active' : ''}"
            style="--pill-accent:${l.color}" onclick="App.filterHsk('${l.id}')">
            HSK ${hskIcons[l.id] || '?'} <span class="pill-count">${done}/${total}</span></span>`;
        })
      ].join('');
    }

    document.getElementById('lessonTopics').innerHTML = [
      `<span class="topic-pill ${!this.activeTopic ? 'active' : ''}" onclick="App.activeTopic=null;App.renderLessons()">Tất cả chủ đề</span>`,
      ...topics.filter(t => t.lessonCount > 0).map(t => {
        const wc = (this.data.vocabulary?.words || []).filter(w => w.topic === t.id).length;
        return `<span class="topic-pill ${this.activeTopic === t.id ? 'active' : ''}" onclick="App.filterTopic('${t.id}')">${t.icon} ${t.name} <span class="pill-count">${t.lessonCount} bài · ${wc} từ</span></span>`;
      })
    ].join('');

    let html = '';
    let idx = 0;
    levels.forEach(level => {
      if (this.activeHsk && level.id !== this.activeHsk) return;
      const lessons = (level.lessons || []).filter(l =>
        !this.activeTopic || l.topic === this.activeTopic
      );
      if (!lessons.length) return;
      html += `<h3 class="lesson-level-head" style="color:${level.color}">${level.name}</h3>`;
      lessons.forEach(lesson => {
        idx++;
        const done = (this.state.completedLessons || []).includes(lesson.id);
        const started = this.state.lessonProgress?.[lesson.id]?.startedAt;
        const topic = topics.find(t => t.id === lesson.topic);
        html += `
          <div class="card card-hover lesson-card" role="button" tabindex="0"
            onclick="App.openLesson('${this.escAttr(lesson.id)}','${this.escAttr(level.id)}')"
            onkeydown="if(event.key==='Enter')App.openLesson('${this.escAttr(lesson.id)}','${this.escAttr(level.id)}')">
            <div class="lesson-num" style="background:${level.color}22;color:${level.color}">${idx}</div>
            <div style="flex:1">
              <div class="flex-between">
                <span class="card-title">${lesson.title}</span>
                ${done ? '<span class="tag tag-done">✓ Hoàn thành</span>' : started ? '<span class="tag">Đang học</span>' : ''}
              </div>
              <div class="card-desc">${level.name}${topic ? ` · ${topic.icon} ${topic.name}` : ''} · ⏱ ${lesson.duration} phút</div>
              <div class="lesson-tags">${(lesson.skills || []).map(sk => `<span class="tag">${sk}</span>`).join('')}</div>
            </div>
          </div>`;
      });
    });
    document.getElementById('lessonList').innerHTML = html ||
      '<div class="empty-state"><div class="empty-icon">📭</div><p>Chưa có bài cho bộ lọc này</p><button class="btn btn-outline mt-2" onclick="App.clearLessonFilters()">Xóa bộ lọc</button></div>';
  },

  openLesson(lessonId, levelId) {
    if (this.currentPage !== 'lessons') this.navigate('lessons');

    const levels = this.data.lessons?.levels || [];
    const level = levels.find(l => l.id === levelId);
    const lesson = level?.lessons?.find(l => l.id === lessonId);
    if (!lesson) {
      console.warn('Lesson not found', lessonId, levelId);
      alert('Không tìm thấy bài học. Thử tải lại trang.');
      return;
    }

    this.selectedLesson = lesson;
    this.selectedLevelId = levelId;
    this.state = Storage.markLessonOpened(lessonId, levelId);

    const vocabIds = [...(lesson.vocabIds || [])];

    const vocabHtml = vocabIds.map(key => {
      let w = this.resolveWord(key);
      if (!w && lesson.content?.dialogue) {
        const line = lesson.content.dialogue.find(d => d.hanzi && d.hanzi.includes(key));
        if (line) {
          w = { hanzi: key, pinyin: line.pinyin, vietnamese: line.vietnamese,
            example: { hanzi: line.hanzi, vietnamese: line.vietnamese } };
        }
      }
      if (!w) return `<div class="text-sm text-muted">⚠ Chưa có trong từ điển: ${key}</div>`;
      const wordKey = w.id || w.hanzi;
      return `
        <div class="vocab-row vocab-row--cn" data-word-id="${wordKey}" ${w.id ? '' : 'data-hanzi-only="1"'} role="button" tabindex="0">
          <div class="dict-hanzi-wrap"><span class="dict-hanzi">${w.hanzi}</span></div>
          <div class="vocab-body">
            <div class="pinyin">${w.pinyin || ''}</div>
            <div class="vocab-meaning">${this.wordMeaning(w).primary}</div>
            ${w.example?.hanzi ? `<div class="vocab-example">${w.example.hanzi} — ${w.example.vietnamese || ''}</div>` : ''}
          </div>
          <button type="button" class="audio-btn" title="Nghe">🔊</button>
        </div>`;
    }).join('') || '<p class="text-muted">Xem hội thoại bên dưới để học từ.</p>';

    const dialogueHtml = (lesson.content?.dialogue || []).map((d, i) => {
      const isB = String(d.speaker).toUpperCase() === 'B';
      const avatar = isB ? '乙' : '甲';
      return `
      <div class="dialogue-bubble dialogue-bubble--${isB ? 'b' : 'a'}">
        <div class="dialogue-avatar">${avatar}</div>
        <div class="dialogue-content">
          <div class="dialogue-speaker">${d.speaker}</div>
          <div class="ruby-text">${this.ruby(d.hanzi, d.pinyin)}</div>
          <div class="dialogue-vi">${d.vietnamese}</div>
        </div>
      </div>`;
    }).join('');

    document.getElementById('lessonDetail').innerHTML = `
      <button class="btn btn-sm btn-outline mb-2" onclick="App.closeLesson()">← Quay lại</button>
      <div class="lesson-detail-head">
        <h2 class="section-title">${lesson.title}</h2>
        <p class="lesson-intro">${lesson.content?.intro || ''}</p>
      </div>
      <h3 class="section-cn-title mb-2"><span class="section-cn-icon">💬</span> Hội thoại</h3>
      <div class="dialogue-stack dialogue-panel mb-3">${dialogueHtml || '<p class="text-muted">Chưa có hội thoại.</p>'}</div>
      <h3 class="section-cn-title mb-2"><span class="section-cn-icon">📚</span> Từ vựng bài học</h3>
      <div class="vocab-panel card mb-3">${vocabHtml}</div>
      <div class="flex gap-1">
        <button class="btn btn-primary" onclick="App.completeLesson('${lesson.id}')">✓ Hoàn thành bài</button>
        <button type="button" class="btn btn-outline" onclick="App.startQuizFromLesson('${this.escAttr(lesson.id)}','${this.escAttr(levelId)}')">📝 Làm quiz</button>
      </div>`;
    const listEl = document.getElementById('lessonList');
    const detailEl = document.getElementById('lessonDetail');
    if (listEl) listEl.classList.add('hidden');
    if (detailEl) {
      detailEl.classList.remove('hidden');
      detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  },

  closeLesson() {
    document.getElementById('lessonList').classList.remove('hidden');
    document.getElementById('lessonDetail').classList.add('hidden');
    this.selectedLesson = null;
  },

  completeLesson(lessonId) {
    const title = this.selectedLesson?.title;
    const levelId = this.selectedLevelId;
    this.state = Storage.markLessonComplete(lessonId, levelId, title);
    this.state = Storage.recalcHskProgress(this.data.lessons?.levels || []);
    this.syncProgressToServer();
    this.renderAll();
    this.closeLesson();
    alert('🎉 Chúc mừng! Bạn đã hoàn thành bài học.');
  },

  ruby(hanzi, pinyin) {
    if (!this.state.settings?.showPinyin) return hanzi;
    const chars = [...hanzi];
    const py = pinyin.split(/\s+/);
    return chars.map((c, i) => {
      if (/[\u4e00-\u9fff]/.test(c)) {
        return `<ruby>${c}<rt>${py[i] || ''}</rt></ruby>`;
      }
      return c;
    }).join('');
  },

  playAudio(text, rate = 0.85) {
    if (typeof Voice === 'undefined') return;
    const hanzi = Voice.toHanzi(text);
    if (!hanzi) {
      console.warn('playAudio: cần chữ Hán, không đọc pinyin:', text);
      return;
    }
    if (rate <= 0.6) return Voice.speakSlow(hanzi);
    if (rate >= 1) return Voice.speakNormal(hanzi);
    return Voice.speak(hanzi, { rate });
  },

  getFilteredVocab() {
    const q = (document.getElementById('vocabSearch')?.value || '').toLowerCase().trim();
    const hsk = document.getElementById('vocabHskFilter')?.value || '';
    const topic = this.activeVocabTopic || '';
    return (this.data.vocabulary?.words || []).filter(w => {
      if (hsk && String(w.hsk) !== hsk) return false;
      if (topic && w.topic !== topic) return false;
      if (!q) return true;
      return w.hanzi.includes(q) ||
        w.pinyin.toLowerCase().includes(q) ||
        (w.vietnamese || '').toLowerCase().includes(q) ||
        (w.english || '').toLowerCase().includes(q);
    });
  },

  renderVocabTopicPills() {
    const el = document.getElementById('vocabTopicPills');
    if (!el) return;
    const topics = this.data.lessons?.topics || [];
    const words = this.data.vocabulary?.words || [];
    const hsk = document.getElementById('vocabHskFilter')?.value || '';
    const counts = {};
    words.forEach(w => {
      if (hsk && String(w.hsk) !== hsk) return;
      if (w.topic) counts[w.topic] = (counts[w.topic] || 0) + 1;
    });
    el.innerHTML = [
      `<span class="topic-pill ${!this.activeVocabTopic ? 'active' : ''}" onclick="App.activeVocabTopic=null;App.vocabPage=0;App.renderVocabulary()">Tất cả chủ đề</span>`,
      ...topics.filter(t => counts[t.id]).map(t =>
        `<span class="topic-pill ${this.activeVocabTopic === t.id ? 'active' : ''}" onclick="App.activeVocabTopic='${t.id}';App.vocabPage=0;App.renderVocabulary()">${t.icon} ${t.name} (${counts[t.id]})</span>`)
    ].join('');
  },

  renderVocabulary() {
    this.renderVocabTopicPills();
    const all = this.getFilteredVocab();
    const page = this.vocabPage || 0;
    const size = this.vocabPageSize || 50;
    const start = page * size;
    const slice = all.slice(start, start + size);
    const total = this.data.vocabulary?.words?.length || 0;
    const topics = this.data.lessons?.topics || [];
    const topicName = topics.find(t => t.id === this.activeVocabTopic)?.name;

    document.getElementById('vocabCountLabel').textContent =
      `${total} từ vựng · Hiển thị ${all.length}${topicName ? ` · ${topicName}` : ''}`;

    document.getElementById('vocabList').innerHTML = slice.map(w => {
      const tMeta = topics.find(t => t.id === w.topic);
      const m = this.wordMeaning(w);
      const sc = w.sentenceCount || (w.sentences?.length || 0);
      const hzCls = this.hanziSizeClass(w.hanzi);
      return `
      <article class="vocab-card" data-word-id="${w.id}" role="button" tabindex="0">
        <div class="vocab-card-hanzi ${hzCls}"><span>${w.hanzi}</span></div>
        <div class="vocab-card-body">
          <div class="vocab-card-chips">${this.vocabCardMeta(w, m, tMeta, sc)}</div>
          <div class="vocab-card-pinyin">${w.pinyin}</div>
          <div class="vocab-card-meaning">${m.primary}</div>
          ${this.sentencePreview(w)}
        </div>
        <button type="button" class="vocab-card-play" title="Nghe" aria-label="Nghe ${w.hanzi}">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M3 10v4h4l5 5V5L7 10H3zm7-.17v6.34L7.83 13H5v-2h2.83L10 9.83zM16.5 12c0-1.77-1.02-3.29-2.5-4.03v8.06c1.48-.74 2.5-2.26 2.5-4.03z"/></svg>
        </button>
      </article>`;
    }).join('') || '<div class="empty-state"><p>Không có từ</p></div>';

    const pages = Math.ceil(all.length / size) || 1;
    document.getElementById('vocabPageInfo').textContent =
      `Trang ${page + 1}/${pages}`;
    document.getElementById('vocabNext').disabled = start + size >= all.length;
    document.getElementById('vocabPrev').disabled = page === 0;
  },

  initVoicePage() {
    const hsk = parseInt(document.getElementById('voiceHskSelect')?.value || '1', 10);
    this.voiceWords = (this.data.vocabulary?.words || []).filter(w => w.hsk === hsk);
    this.voiceIndex = 0;
    Voice.setPlaylist(this.voiceWords);
    const ttsSel = document.getElementById('ttsEngine');
    if (ttsSel) ttsSel.value = Voice.engine || 'youdao';
    this.showVoiceWord();
  },

  showVoiceWord() {
    const w = this.voiceWords?.[this.voiceIndex];
    if (!w) return;
    document.getElementById('voiceWordHanzi').textContent = w.hanzi;
    document.getElementById('voiceWordPinyin').textContent = w.pinyin;
    document.getElementById('voiceWordVi').textContent = w.vietnamese + (w.english ? ` (${w.english})` : '');
  },

  voicePlayCurrent(mode) {
    const w = this.voiceWords?.[this.voiceIndex];
    if (!w) return;
    if (mode === 'hanzi') Voice.speak(w.hanzi);
    else if (mode === 'slow') Voice.speakSlow(w.hanzi);
    else Voice.speakNormal(w.hanzi);
  },

  voicePrev() {
    if (!this.voiceWords?.length) return;
    this.voiceIndex = (this.voiceIndex - 1 + this.voiceWords.length) % this.voiceWords.length;
    this.showVoiceWord();
  },

  voiceNext() {
    if (!this.voiceWords?.length) return;
    this.voiceIndex = (this.voiceIndex + 1) % this.voiceWords.length;
    this.showVoiceWord();
  },

  async voicePlayAll() {
    Voice.setPlaylist(this.voiceWords);
    document.getElementById('loading')?.classList.remove('hidden');
    const el = document.querySelector('#loading p');
    if (el) el.textContent = 'Đang phát danh sách từ...';
    await Voice.playAll(2200);
    document.getElementById('loading')?.classList.add('hidden');
    if (el) el.textContent = 'Đang tải dữ liệu...';
  },

  /* ===== Flashcards ===== */
  renderFlashcards() {
    const vocab = this.data.vocabulary?.words || [];
    this.dueCards = SRS.getDueCards(vocab, this.state.srsCards || {});
    const stats = SRS.getStats(this.state.srsCards || {});

    document.getElementById('srsStats').innerHTML = `
      <span class="topic-pill">📊 Tổng: ${stats.total || vocab.length}</span>
      <span class="topic-pill" style="background:var(--primary-light);color:var(--primary)">⏳ Cần ôn: ${this.dueCards.length}</span>
      <span class="topic-pill">✅ Thuần: ${stats.mastered}</span>
    `;

    if (!this.dueCards.length) {
      document.getElementById('flashcardArea').innerHTML = `
        <div class="empty-state card">
          <div class="empty-icon">🎉</div>
          <h3>Đã ôn hết thẻ hôm nay!</h3>
          <p class="text-muted mb-2">Học bài mới để thêm từ vào SRS</p>
          <button class="btn btn-primary" onclick="App.navigate('lessons')">Đi học bài mới</button>
        </div>`;
      return;
    }

    this.flashcardIndex = 0;
    this.showFlashcard();
  },

  showFlashcard() {
    const w = this.dueCards[this.flashcardIndex];
    if (!w) { this.renderFlashcards(); return; }

    document.getElementById('flashcardArea').innerHTML = `
      <div class="flashcard-wrap">
        <div class="flashcard" id="fcard" onclick="document.getElementById('fcard').classList.toggle('flipped')">
          <div class="flashcard-face flashcard-front">
            <div class="hanzi hanzi-xl">${w.hanzi}</div>
            <p class="text-muted mt-2">Nhấn để lật thẻ</p>
          </div>
          <div class="flashcard-face flashcard-back">
            <div class="pinyin">${w.pinyin}</div>
            <div style="font-size:1.15rem;margin:12px 0;font-weight:600">${w.vietnamese}</div>
            <div class="text-sm text-muted">${w.example?.hanzi}</div>
            <div class="text-sm">${w.example?.vietnamese}</div>
            <button class="btn btn-sm btn-outline mt-2" onclick="event.stopPropagation();App.playWord('${this.escAttr(w.id)}')">🔊 Nghe</button>
          </div>
        </div>
      </div>
      <div class="srs-row">
        <button class="srs-btn again" onclick="App.rateCard(0)">Quên</button>
        <button class="srs-btn hard" onclick="App.rateCard(1)">Khó</button>
        <button class="srs-btn good" onclick="App.rateCard(2)">Đúng</button>
        <button class="srs-btn easy" onclick="App.rateCard(3)">Dễ</button>
      </div>
      <p class="text-center text-muted text-sm mt-2">${this.flashcardIndex + 1} / ${this.dueCards.length}</p>`;
  },

  rateCard(quality) {
    const w = this.dueCards[this.flashcardIndex];
    const srs = this.state.srsCards || {};
    let card = SRS.getCard(w.id, srs);
    card = SRS.review(card, quality);
    srs[w.id] = card;
    this.state = Storage.update({ srsCards: srs });
    if (quality >= 2) {
      const data = Storage.get();
      data.wordsLearned = (data.wordsLearned || 0) + 1;
      Storage.save(data);
    }
    this.flashcardIndex++;
    if (this.flashcardIndex >= this.dueCards.length) {
      this.renderFlashcards();
    } else {
      this.showFlashcard();
    }
    this.updateTopBar();
  },

  /* ===== Skills ===== */
  renderSkills() {
    this.setSkill('listen');
  },

  setSkill(skill) {
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.skill === skill));
    const vocab = this.data.vocabulary?.words || [];
    const w = vocab[Math.floor(Math.random() * vocab.length)] || vocab[0];
    const titles = { listen: '👂 Nghe', read: '📖 Đọc', write: '✍️ Viết', speak: '🗣️ Nói' };
    let body = '';

    switch (skill) {
      case 'listen':
        body = `<div class="hanzi hanzi-lg text-center mb-2">???</div>
          <p class="text-center mb-2">Nhấn nghe rồi chọn đáp án</p>
          <button class="btn btn-primary" style="display:block;margin:0 auto" onclick="Voice.speak('${this.escAttr(w.hanzi)}')">🔊 Phát âm</button>
          <div class="mt-2 grid-2">${[w.vietnamese, 'Tạm biệt', 'Cảm ơn', 'Xin lỗi'].sort(() => Math.random() - .5).map(o =>
            `<button class="quiz-option" onclick="alert('${o === w.vietnamese ? '✓ Đúng!' : '✗ Sai'}')">${o}</button>`).join('')}</div>`;
        break;
      case 'read':
        body = `<div class="ruby-text text-center" style="font-size:2rem">${this.ruby(w.example?.hanzi || w.hanzi, w.pinyin)}</div>
          <p class="text-center mt-2">${w.example?.vietnamese || w.vietnamese}</p>`;
        break;
      case 'write':
        body = `<div class="hanzi hanzi-xl text-center" style="border:2px dashed var(--border);border-radius:16px;padding:32px;background:var(--bg)">${w.hanzi}</div>
          <p class="text-center text-muted mt-2">Luyện viết: ${w.hanzi} (${w.strokeCount} nét)</p>
          <p class="text-center text-sm">💡 Premium: hướng dẫn nét chữ tương tác</p>`;
        break;
      case 'speak':
        body = `<div class="hanzi hanzi-lg text-center">${w.hanzi}</div>
          <p class="text-center pinyin mb-2">${w.pinyin}</p>
          <button class="btn btn-accent btn-lg" id="recordBtn2" onclick="App.toggleRecording()">🎙️ Ghi âm & luyện nói</button>
          <p class="text-center text-sm text-muted mt-2" id="recordStatus">Nhấn để bắt đầu ghi âm</p>`;
        break;
    }

    document.getElementById('skillTitle').textContent = titles[skill];
    document.getElementById('skillBody').innerHTML = body;
  },

  toggleRecording() {
    const el = document.getElementById('recordStatus');
    if (!this.recording) {
      if (!navigator.mediaDevices) {
        alert('Trình duyệt không hỗ trợ ghi âm. Dùng Chrome/Safari.');
        return;
      }
      this.recording = true;
      if (el) el.textContent = '🔴 Đang ghi... Nhấn lại để dừng';
      navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
        this.mediaStream = stream;
        setTimeout(() => {
          if (this.recording) {
            stream.getTracks().forEach(t => t.stop());
            this.recording = false;
            if (el) el.textContent = '✓ Đã ghi! (Premium: AI chấm điểm phát âm)';
          }
        }, 3000);
      }).catch(() => alert('Cần quyền microphone.'));
    } else {
      this.recording = false;
      this.mediaStream?.getTracks().forEach(t => t.stop());
      if (el) el.textContent = '✓ Đã ghi xong!';
    }
  },

  /* ===== Quiz ===== */
  endQuiz() {
    this.quizInProgress = false;
    this.quizState = null;
    this.renderQuiz();
  },

  renderQuiz() {
    this.quizInProgress = false;
    const quizzes = this.data.quizzes?.quizzes || [];
    const words = this.data.vocabulary?.words || [];
    const matrix = this.data.examMatrix || {};
    const levelSpec = matrix.levels || {};
    const totalQ = quizzes.reduce((s, q) => s + (q.questions?.length || 0), 0);
    const targetTotal = Object.values(levelSpec).reduce((s, lv) => {
      const types = lv.exam_types || {};
      return s + Object.values(types).reduce((t, et) => t + (et.count || 0), 0);
    }, 0);

    const statsEl = document.getElementById('quizStats');
    if (statsEl) {
      statsEl.innerHTML = `
        <div class="exam-stat-item"><strong>${quizzes.length}/${targetTotal || '—'}</strong><span>Đề hiện có / mục tiêu</span></div>
        <div class="exam-stat-item"><strong>${totalQ}</strong><span>Câu hỏi</span></div>
        <div class="exam-stat-item"><strong>9</strong><span>Cấp HSK 3.0</span></div>`;
    }

    const tabsEl = document.getElementById('quizHskTabs');
    if (tabsEl) {
      const stages = matrix.stages || {};
      let tabsHtml = `<button type="button" class="hsk-exam-tab ${!this.activeQuizHsk ? 'active' : ''}" data-hsk="">Tất cả<small>${quizzes.length} đề</small></button>`;

      Object.entries(stages).forEach(([stageKey, stage]) => {
        tabsHtml += `<div class="hsk-stage-label">${stage.label_vi || stageKey}</div>`;
        (stage.levels || []).forEach(levelId => {
          const spec = levelSpec[levelId] || {};
          const n = spec.num || levelId.replace('hsk', '');
          const have = quizzes.filter(q => q.level === levelId).length;
          const target = Object.values(spec.exam_types || {}).reduce((s, et) => s + (et.count || 0), 0);
          const planned = spec.status === 'planned';
          const partial = have < target && !planned;
          const badge = planned ? 'Sắp ra' : partial ? `${have}/${target}` : `${have} đề`;
          tabsHtml += `<button type="button" class="hsk-exam-tab ${this.activeQuizHsk === levelId ? 'active' : ''} ${planned ? 'hsk-exam-tab--planned' : ''}" data-hsk="${levelId}" ${planned ? 'disabled' : ''}>HSK ${n}<small>${badge}</small></button>`;
        });
      });

      tabsEl.innerHTML = tabsHtml;
      tabsEl.querySelectorAll('.hsk-exam-tab:not([disabled])').forEach(btn => {
        btn.addEventListener('click', () => {
          this.activeQuizHsk = btn.dataset.hsk || null;
          this.renderQuiz();
        });
      });
    }

    const activeSpec = this.activeQuizHsk ? levelSpec[this.activeQuizHsk] : null;
    const filtered = this.activeQuizHsk
      ? quizzes.filter(q => q.level === this.activeQuizHsk)
      : quizzes;

    let listHtml = '';
    if (activeSpec && filtered.length === 0) {
      const types = activeSpec.exam_types || {};
      const typeLines = Object.entries(types).map(([k, v]) =>
        `• ${k}: ${v.count} đề × ${v.questions} câu (${v.minutes} phút)`).join('<br>');
      listHtml = `<div class="empty-state card" style="grid-column:1/-1">
        <h3>${activeSpec.name_vi || activeSpec.name}</h3>
        <p class="text-muted mb-2">Đang bổ sung đề theo ma trận PO. Mục tiêu:</p>
        <p class="text-sm">${typeLines}</p>
        <p class="text-sm text-muted mt-2">Từ vựng mục tiêu: ${activeSpec.vocab_target?.toLocaleString() || '—'} từ</p>
        <button class="btn btn-outline btn-sm mt-2" onclick="App.navigate('lessons')">Học bài trước →</button>
      </div>`;
    } else {
      listHtml = filtered.map(q => `
      <div class="card card-hover" role="button" tabindex="0"
        onclick="App.startQuiz('${this.escAttr(q.id)}')"
        onkeydown="if(event.key==='Enter')App.startQuiz('${this.escAttr(q.id)}')">
        <div class="card-title">📝 ${q.title}</div>
        <div class="card-desc">${q.questions.length} câu · ${q.level ? q.level.toUpperCase() : 'HSK'} · Có giải thích</div>
        <span class="btn btn-primary btn-sm mt-2">Làm bài thử →</span>
      </div>`).join('') ||
        '<div class="empty-state" style="grid-column:1/-1"><p>Chọn cấp HSK 1–9 ở trên</p></div>';
    }

    document.getElementById('quizList').innerHTML = listHtml;
    document.getElementById('quizArea').classList.add('hidden');
    document.getElementById('quizList').classList.remove('hidden');
  },

  startQuizFromLesson(lessonId, levelId) {
    const levelNum = (levelId || '').replace('hsk', '') || '1';
    const quizzes = this.data.quizzes?.quizzes || [];
    let quiz = quizzes.find(q => q.lessonId === lessonId);
    if (!quiz) {
      quiz = quizzes.find(q => q.id === `quiz-hsk${levelNum}-1` || q.level === levelId);
    }
    if (!quiz) quiz = quizzes.find(q => String(q.level) === `hsk${levelNum}`);
    if (!quiz) quiz = quizzes[0];
    this.startQuiz(quiz?.id, { fromLesson: true });
  },

  startQuiz(quizIdOrLesson, opts = {}) {
    const quizzes = this.data.quizzes?.quizzes || [];
    const quiz = quizzes.find(q => q.id === quizIdOrLesson || q.lessonId === quizIdOrLesson)
      || quizzes[0];
    if (!quiz) {
      alert('Chưa có đề quiz. Vào mục Luyện đề để chọn bài.');
      return;
    }

    this.quizState = { quiz, index: 0, score: 0, answered: false };
    this.quizInProgress = true;

    if (this.currentPage !== 'quiz') {
      this.navigate('quiz');
    } else {
      const listEl = document.getElementById('quizList');
      const areaEl = document.getElementById('quizArea');
      if (listEl) listEl.classList.add('hidden');
      if (areaEl) areaEl.classList.remove('hidden');
      this.showQuizQuestion();
    }
    document.getElementById('quizArea')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  },

  showQuizQuestion() {
    const { quiz, index } = this.quizState;
    const q = quiz.questions[index];
    if (!q) {
      document.getElementById('quizArea').innerHTML = `
        <div class="card" style="text-align:center;padding:2rem">
          <h2>🎉 Hoàn thành!</h2>
          <p>Điểm: ${this.quizState.score}/${quiz.questions.length}</p>
          <button type="button" class="btn btn-primary mt-2" onclick="App.endQuiz()">← Quay lại</button>
        </div>`;
      const scores = Storage.get().quizScores || {};
      scores[quiz.id] = this.quizState.score;
      Storage.update({ quizScores: scores });
      this.quizInProgress = false;
      return;
    }

    document.getElementById('quizArea').innerHTML = `
      <div class="card">
        <div class="text-sm text-muted mb-1">Câu ${index + 1}/${quiz.questions.length}</div>
        <h3 class="mb-2">${q.question}</h3>
        ${q.audioText ? `<button type="button" class="btn btn-sm btn-outline mb-2" onclick="Voice.speak('${this.escAttr(q.audioText)}')">🔊 Nghe</button>` : ''}
        <div id="quizOptions">
          ${q.options.map((o, i) => `<button type="button" class="quiz-option" onclick="App.answerQuiz(${i})">${o.replace(/</g, '&lt;')}</button>`).join('')}
        </div>
        <div id="quizFeedback" class="mt-2 hidden"></div>
        <button class="btn btn-primary mt-2 hidden" id="quizNext" onclick="App.nextQuiz()">Tiếp →</button>
      </div>`;
    this.quizState.answered = false;
  },

  answerQuiz(choice) {
    if (this.quizState.answered) return;
    const q = this.quizState.quiz.questions[this.quizState.index];
    const correct = choice === q.correct;
    this.quizState.answered = true;
    if (correct) this.quizState.score++;

    document.querySelectorAll('.quiz-option').forEach((btn, i) => {
      btn.disabled = true;
      if (i === q.correct) btn.classList.add('correct');
      if (i === choice && !correct) btn.classList.add('wrong');
    });

    const fb = document.getElementById('quizFeedback');
    fb.classList.remove('hidden');
    fb.innerHTML = `<p style="color:${correct ? 'var(--success)' : '#ef4444'}">${correct ? '✓ Chính xác!' : '✗ Sai rồi'}</p>
      ${q.explanation ? `<p class="text-sm text-muted">${q.explanation}</p>` : ''}`;
    document.getElementById('quizNext').classList.remove('hidden');
  },

  nextQuiz() {
    this.quizState.index++;
    this.showQuizQuestion();
  },

  /* ===== Dictionary ===== */
  renderDictionary(query = '') {
    const entries = this.data.dictionary?.entries ||
      (this.data.vocabulary?.words || []).map(w => ({
        hanzi: w.hanzi, pinyin: w.pinyin, vietnamese: w.vietnamese, hsk: w.hsk,
        pos: `HSK ${w.hsk}`, examples: [w.example?.hanzi].filter(Boolean)
      }));
    const q = query.toLowerCase().trim();
    const filtered = (q
      ? entries.filter(e =>
          e.hanzi.includes(q) ||
          e.pinyin.toLowerCase().includes(q) ||
          (e.vietnamese || '').toLowerCase().includes(q)
        )
      : entries
    ).slice(0, 80);

    document.getElementById('dictResults').innerHTML = filtered.map(e => {
      const m = this.wordMeaning(e);
      return `
      <div class="dict-item dict-item--cn" data-word-id="${e.hanzi}" data-hanzi-only="1" role="button" tabindex="0">
        <div class="dict-hanzi-wrap"><span class="dict-hanzi">${e.hanzi}</span></div>
        <div class="dict-body">
          <div class="dict-top">
            <span class="pinyin">${e.pinyin}</span>
            <span class="tag tag-hsk">HSK ${e.hsk}</span>
          </div>
          <div class="dict-meaning">${m.primary}</div>
          <div class="dict-sub">${e.pos} · ${e.examples?.[0] || ''}</div>
        </div>
        <button class="audio-btn" title="Nghe" aria-label="Nghe ${e.hanzi}">🔊</button>
      </div>`;
    }).join('') ||
      '<div class="empty-state" style="padding:32px"><p>Không tìm thấy từ</p></div>';
  },

  youtubeEmbedUrl(videoId, opts = {}) {
    if (!videoId || videoId.startsWith('PLACEHOLDER')) return '';
    const params = new URLSearchParams({
      rel: '0',
      modestbranding: '1',
      playsinline: '1'
    });
    if (opts.list) params.set('list', opts.list);
    const origin = typeof location !== 'undefined' && location.origin && location.origin !== 'null'
      ? location.origin : '';
    if (origin) params.set('origin', origin);
    return `https://www.youtube-nocookie.com/embed/${videoId}?${params}`;
  },

  youtubeWatchUrl(videoId) {
    return `https://www.youtube.com/watch?v=${videoId}`;
  },

  /* ===== Videos ===== */
  renderVideoCard(v) {
    const id = v.youtubeId || '';
    const invalid = !id || id.startsWith('PLACEHOLDER');
    const isLocked = (!v.free && !this.isPremium()) || invalid;
    const thumb = !invalid
      ? `https://i.ytimg.com/vi/${id}/mqdefault.jpg`
      : 'linear-gradient(135deg,#1e40af,#0891b2)';
    const bgStyle = thumb.startsWith('http') ? `url('${thumb}') center/cover` : thumb;
    return `
      <div class="card video-card card-hover" role="button" tabindex="0"
        data-youtube-id="${this.escAttr(id)}"
        data-video-title="${this.escAttr(v.title)}"
        data-video-locked="${isLocked ? '1' : '0'}"
        onclick="App.playVideoFromEl(this)"
        onkeydown="if(event.key==='Enter')App.playVideoFromEl(this)">
        <div class="video-thumb" style="background:${bgStyle}">
          ${isLocked
            ? '<div class="video-lock-overlay">🔒<span class="text-sm">VIP</span></div>'
            : '<div class="video-play"><span>▶</span></div>'}
        </div>
        <div class="video-body">
          <div class="card-title">${this.escHtml(v.title)}</div>
          <div class="card-desc">${v.duration || ''} · ${v.level || ''}${v.free ? ' · Miễn phí' : ' · VIP'}</div>
        </div>
      </div>`;
  },

  renderVideos() {
    const vdata = this.data.videos || {};
    const fp = vdata.featuredPlaylist;
    const embedEl = document.getElementById('videoPlaylistEmbed');

    if (embedEl && fp) {
      const plEmbed = this.youtubeEmbedUrl('videoseries', { list: fp.id });
      embedEl.innerHTML = `
        <div class="card mb-3" style="padding:0;overflow:hidden">
          <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <h3 class="card-title">📺 ${this.escHtml(fp.title)}</h3>
            <p class="card-desc">Phát cả playlist — <a href="${fp.url}" target="_blank" rel="noopener" style="color:var(--primary)">Mở trên YouTube</a></p>
          </div>
          <div class="video-embed" style="border-radius:0">
            <iframe src="${plEmbed || fp.embedUrl}" title="Playlist HSK"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
          </div>
        </div>`;
    }

    const playlists = vdata.playlists || [];
    let freeHtml = '';
    let vipHtml = '';

    playlists.forEach(pl => {
      if (pl.embedPlaylist && (!pl.videos || !pl.videos.length)) return;
      if (!pl.videos?.length) return;
      const header = `<h3 class="card-title mb-2 mt-3">${this.escHtml(pl.name)} ${pl.premium ? '<span class="pro-badge">PRO</span>' : ''}</h3>`;
      let plFree = '';
      let plVip = '';
      (pl.videos || []).forEach(v => {
        const card = this.renderVideoCard(v);
        if (v.free) plFree += card;
        else plVip += card;
      });
      if (plFree) freeHtml += header + plFree;
      if (plVip) vipHtml += header + plVip;
    });

    document.getElementById('videoGrid').innerHTML = freeHtml ||
      '<div class="empty-state"><p>Chưa có video miễn phí</p></div>';
    const vipEl = document.getElementById('vipVideoGrid');
    if (vipEl) {
      vipEl.innerHTML = vipHtml ||
        '<div class="empty-state"><p>Video VIP sẽ hiện khi có dữ liệu</p></div>';
    }
    document.getElementById('vipVideoHead')?.classList.toggle('hidden', !vipHtml);
    document.getElementById('videoPlayer').classList.add('hidden');
    document.getElementById('videoGrid').classList.remove('hidden');
    document.getElementById('vipVideoGrid')?.classList.remove('hidden');
  },

  playVideoFromEl(el) {
    const id = el?.dataset?.youtubeId;
    const title = el?.dataset?.videoTitle || '';
    const locked = el?.dataset?.videoLocked === '1';
    this.playVideo(id, title, locked);
  },

  playVideo(youtubeId, title, locked) {
    if (locked) {
      this.showUpgradeModal();
      return;
    }
    if (!youtubeId || youtubeId.startsWith('PLACEHOLDER')) {
      alert('Video không khả dụng. Vui lòng chọn video khác.');
      return;
    }
    const embedSrc = this.youtubeEmbedUrl(youtubeId);
    const watchUrl = this.youtubeWatchUrl(youtubeId);
    document.getElementById('videoGrid').classList.add('hidden');
    document.getElementById('vipVideoGrid')?.classList.add('hidden');
    const player = document.getElementById('videoPlayer');
    player.classList.remove('hidden');
    player.innerHTML = `
      <button class="btn btn-sm btn-outline mb-2" onclick="App.renderVideos()">← Danh sách</button>
      <h3 class="mb-2">${this.escHtml(title)}</h3>
      <div class="video-embed">
        <iframe src="${embedSrc}" title="${this.escAttr(title)}"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowfullscreen referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
      </div>
      <p class="text-sm text-muted mt-2">Video không hiện? <a href="${watchUrl}" target="_blank" rel="noopener" style="color:var(--primary)">Mở trên YouTube ↗</a></p>`;
    player.scrollIntoView({ behavior: 'smooth', block: 'start' });
  },

  escHtml(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  },

  /* ===== Roadmap & Exam tips ===== */
  renderRoadmap() {
    const rm = this.data.roadmap || {};
    const sub = document.getElementById('roadmapSubtitle');
    if (sub) sub.textContent = rm.subtitle || '';
    const phasesEl = document.getElementById('roadmapPhases');
    if (!phasesEl) return;
    phasesEl.innerHTML = (rm.phases || []).map((ph, i) => `
      <div class="roadmap-phase card mb-2" style="--phase-color:${ph.color || 'var(--primary)'}">
        <div class="roadmap-phase-head">
          <span class="roadmap-week">${ph.weeks}</span>
          <h3>${this.escHtml(ph.title)}</h3>
        </div>
        <p class="text-sm text-muted mb-2">${(ph.goals || []).join(' · ')}</p>
        <ul class="roadmap-tasks">
          ${(ph.tasks || []).map(t => `
            <li><button type="button" class="roadmap-task-btn" onclick="App.navigate('${t.action}')">${this.escHtml(t.label)}</button></li>
          `).join('')}
        </ul>
      </div>`).join('');

    const cta = rm.premium_upsell || {};
    const ctaEl = document.getElementById('roadmapPremiumCta');
    if (ctaEl) {
      if (this.isPremium()) {
        ctaEl.innerHTML = `<div class="card-title">✨ Bạn đang dùng Premium</div><p class="card-desc">Xem lộ trình AI cá nhân tại mục Lộ trình AI.</p>
          <button class="btn btn-primary mt-2" onclick="App.navigate('personalized')">Mở lộ trình AI</button>`;
      } else {
        ctaEl.innerHTML = `<div class="card-title">${this.escHtml(cta.title || 'Premium')}</div>
          <ul class="text-sm text-muted mt-2" style="padding-left:1.2rem;line-height:1.8">
            ${(cta.points || []).map(p => `<li>${this.escHtml(p)}</li>`).join('')}
          </ul>
          <button class="btn btn-primary mt-2" data-upgrade>Nâng cấp Premium</button>`;
        ctaEl.querySelector('[data-upgrade]')?.addEventListener('click', () => this.showUpgradeModal());
      }
    }
  },

  renderExamTips() {
    const tips = this.data.examTips || {};
    const matrix = this.data.examMatrix?.levels || {};
    const tabsEl = document.getElementById('examTipsTabs');
    if (tabsEl) {
      tabsEl.innerHTML = Object.keys(tips.levels || {}).map(levelId => {
        const n = levelId.replace('hsk', '');
        const active = this.activeExamTipsHsk === levelId ? 'active' : '';
        return `<button type="button" class="hsk-exam-tab ${active}" data-tips-hsk="${levelId}">HSK ${n}</button>`;
      }).join('');
      tabsEl.querySelectorAll('[data-tips-hsk]').forEach(btn => {
        btn.addEventListener('click', () => {
          this.activeExamTipsHsk = btn.dataset.tipsHsk;
          this.renderExamTips();
        });
      });
    }

    const genEl = document.getElementById('examTipsGeneral');
    if (genEl) {
      genEl.innerHTML = (tips.general || []).map(t => `
        <div class="feature-card">
          <div class="feat-icon">${t.icon}</div>
          <h3>${this.escHtml(t.title)}</h3>
          <p>${this.escHtml(t.body)}</p>
        </div>`).join('');
    }

    const lv = tips.levels?.[this.activeExamTipsHsk] || {};
    const spec = matrix[this.activeExamTipsHsk] || {};
    const lvEl = document.getElementById('examTipsLevel');
    if (lvEl) {
      lvEl.innerHTML = `
        <div class="card exam-tips-level">
          <h3 class="card-title">HSK ${spec.num || this.activeExamTipsHsk.replace('hsk', '')} — Mục tiêu ${this.escHtml(lv.target_score || '')}</h3>
          <p class="card-desc mb-2">${this.escHtml(lv.focus || '')}</p>
          <div class="grid-2 exam-tips-meta">
            <div><strong>Thời gian ôn</strong><br>${this.escHtml(lv.prep_weeks || '')}</div>
            <div><strong>Kế hoạch/ngày</strong><br>${this.escHtml(lv.daily_plan || '')}</div>
          </div>
          <h4 class="card-title mt-3 mb-1">Lỗi thường gặp</h4>
          <ul class="text-sm text-muted" style="padding-left:1.2rem;line-height:1.8">
            ${(lv.mistakes || []).map(m => `<li>${this.escHtml(m)}</li>`).join('')}
          </ul>
          <div class="flex gap-2 mt-3 flex-wrap">
            <button class="btn btn-primary btn-sm" onclick="App.activeQuizHsk='${this.activeExamTipsHsk}';App.navigate('quiz')">Làm đề HSK ${spec.num || ''}</button>
            <button class="btn btn-outline btn-sm" onclick="App.filterHsk('${this.activeExamTipsHsk}')">Học bài ${this.activeExamTipsHsk.toUpperCase()}</button>
          </div>
        </div>`;
    }

    const skEl = document.getElementById('examTipsSkills');
    if (skEl) {
      skEl.innerHTML = Object.entries(tips.skills || {}).map(([key, sk]) => `
        <div class="card">
          <h3 class="card-title">${this.escHtml(sk.title)}</h3>
          <ul class="text-sm text-muted" style="padding-left:1.2rem;line-height:1.8">
            ${(sk.tips || []).map(t => `<li>${this.escHtml(t)}</li>`).join('')}
          </ul>
        </div>`).join('');
    }
  },

  /* ===== Premium ===== */
  renderPremium() {
    const p = this.data.premium;
    const cmp = this.data.premiumCompare || {};
    document.getElementById('pricingCards').innerHTML = `
      <div class="card premium-card">
        <div class="card-title">Gói tháng</div>
        <div class="price-tag">${p.pricing.monthly.label}</div>
        <button class="btn btn-outline mt-2" data-upgrade>Nâng cấp</button>
      </div>
      <div class="card premium-card featured">
        <div class="ribbon">BEST</div>
        <div class="card-title">Gói năm</div>
        <div class="price-tag">${p.pricing.yearly.label}</div>
        <div class="text-sm text-muted">${p.pricing.yearly.savings}</div>
        <button class="btn btn-primary mt-2" data-upgrade>Mua ngay</button>
      </div>`;

    const compareEl = document.getElementById('premiumCompare');
    if (compareEl) {
      compareEl.innerHTML = `
        <h3 class="card-title mb-2">Free vs Premium</h3>
        <div class="premium-compare-grid">
          <div><strong>Miễn phí</strong><ul>${(cmp.free || []).map(x => `<li>${this.escHtml(x)}</li>`).join('')}</ul></div>
          <div class="premium-compare-pro"><strong>👑 Premium</strong><ul>${(cmp.pro || []).map(x => `<li>${this.escHtml(x)}</li>`).join('')}</ul></div>
        </div>`;
    }

    document.getElementById('premiumFeatures').innerHTML = p.features.map(f => `
      <div class="card card-hover" style="cursor:pointer" onclick="App.openPremiumFeature('${f.id}')">
        <div style="font-size:2rem;margin-bottom:12px">${f.icon}</div>
        <div class="card-title">${f.title}</div>
        <div class="card-desc">${f.tagline}</div>
        <ul class="text-sm text-muted mt-2" style="padding-left:1.2rem;line-height:1.8">
          ${f.highlights.slice(0, 3).map(h => `<li>${h}</li>`).join('')}
        </ul>
      </div>`).join('');

    document.querySelectorAll('[data-upgrade]').forEach(btn => {
      btn.onclick = () => this.showUpgradeModal();
    });
  },

  openPremiumFeature(id) {
    const map = { 'ai-tutor': 'ai-tutor', pronunciation: 'pronunciation', personalized: 'personalized', exclusive: 'videos' };
    this.navigate(map[id] || 'premium');
  },

  renderAiTutor() {
    if (!this.isPremium()) {
      document.getElementById('aiTutorGate').classList.remove('hidden');
      document.getElementById('aiTutorContent').classList.add('hidden');
      return;
    }
    document.getElementById('aiTutorGate').classList.add('hidden');
    document.getElementById('aiTutorContent').classList.remove('hidden');
    this.populateAiScenarios();
  },

  initAiTutorUi() {
    document.getElementById('aiMode')?.addEventListener('change', e => {
      this._aiMode = e.target.value;
      document.getElementById('aiScenario')?.classList.toggle('hidden', this._aiMode !== 'roleplay');
    });
    document.getElementById('aiHskLevel')?.addEventListener('change', e => {
      this._aiHskLevel = e.target.value;
    });
    document.getElementById('aiScenario')?.addEventListener('change', e => {
      this._aiScenario = e.target.value;
    });
    document.querySelectorAll('[data-ai-prompt]').forEach(pill => {
      pill.addEventListener('click', () => {
        const input = document.getElementById('chatInput');
        if (input) input.value = pill.dataset.aiPrompt || '';
      });
    });
  },

  populateAiScenarios() {
    const sel = document.getElementById('aiScenario');
    if (!sel) return;
    const scenarios = this.data.premium?.roleplayScenarios || [];
    sel.innerHTML = scenarios.map(s => `<option value="${s.id}">${this.escHtml(s.title)}</option>`).join('');
    if (scenarios.length) this._aiScenario = scenarios[0].id;
  },

  getAiChatOptions() {
    return {
      mode: document.getElementById('aiMode')?.value || this._aiMode || 'tutor',
      scenario_id: (document.getElementById('aiMode')?.value === 'roleplay')
        ? (document.getElementById('aiScenario')?.value || this._aiScenario)
        : null,
      hsk_level: document.getElementById('aiHskLevel')?.value || this._aiHskLevel || 'hsk1',
    };
  },

  sendChat() {
    const input = document.getElementById('chatInput');
    const msg = input.value.trim();
    if (!msg) return;

    const box = document.getElementById('chatBox');
    box.innerHTML += `<div class="chat-bubble user">${msg}</div>`;
    input.value = '';
    box.scrollTop = box.scrollHeight;

    const appendReply = (text, sub = '') => {
      box.innerHTML += `<div class="chat-bubble ai"><strong>${text}</strong>${sub ? `<br><span class="text-sm text-muted">${sub}</span>` : ''}</div>`;
      box.scrollTop = box.scrollHeight;
    };

    if (this._apiOnline && HanVietAPI.token && this.isPremium()) {
      HanVietAPI.aiChat(msg, this._chatSessionId, this.getAiChatOptions())
        .then((res) => {
          this._chatSessionId = res.session_id;
          const sub = res.metadata?.rag
            ? `🔍 RAG · ${res.metadata.rag_count || 0} mẩu từ kho học liệu`
            : '';
          appendReply(res.reply, sub);
        })
        .catch((err) => {
          if (err.code === 'premium_required') {
            appendReply('🔒 Cần Premium để dùng AI Tutor đầy đủ.');
          } else {
            appendReply('AI tạm thời không phản hồi. Thử lại sau.');
          }
        });
      return;
    }

    const responses = [
      { zh: '你说得很好！', vi: 'Bạn nói rất tốt!' },
      { zh: '注意声调。"你好" 应该是 nǐ hǎo (3-3)。', vi: 'Chú ý thanh điệu.' },
      { zh: '可以试试说：我很高兴认识你。', vi: 'Thử nói: Tôi rất vui được gặp bạn.' }
    ];
    const r = responses[Math.floor(Math.random() * responses.length)];
    setTimeout(() => appendReply(r.zh, r.vi), 600);
  },

  renderPronunciation() {
    const gate = document.getElementById('pronGate');
    const content = document.getElementById('pronContent');
    if (!this.isPremium()) {
      gate?.classList.remove('hidden');
      content?.classList.add('hidden');
    } else {
      gate?.classList.add('hidden');
      content?.classList.remove('hidden');
    }
  },

  renderPersonalized() {
    const gate = document.getElementById('persGate');
    const content = document.getElementById('persContent');
    if (!this.isPremium()) {
      gate?.classList.remove('hidden');
      content?.classList.add('hidden');
    } else {
      gate?.classList.add('hidden');
      content?.classList.remove('hidden');
      const s = this.state;
      const levels = this.data.lessons?.levels || [];
      const weak = (this.data.vocabulary?.words || [])
        .filter(w => !(s.learnedWords || []).includes(w.id))
        .slice(0, 8);
      const lowHsk = levels.map(l => {
        const done = (s.completedLessons || []).filter(id => id.startsWith(l.id)).length;
        const total = l.lessons?.length || 1;
        return { l, pct: Math.round((done / total) * 100) };
      }).sort((a, b) => a.pct - b.pct)[0];

      document.getElementById('learningPath').innerHTML = `
        <div class="card mb-2 roadmap-phase">
          <div class="card-title">Tuần này — AI đề xuất</div>
          <div class="card-desc">Ưu tiên ${lowHsk ? lowHsk.l.name : 'HSK 1'} (${lowHsk?.pct || 0}% hoàn thành)</div>
        </div>
        <div class="card mb-2">
          <div class="card-title">Ôn ${weak.length} từ yếu</div>
          <div class="card-desc">${weak.map(w => w.hanzi).join(', ') || '—'}</div>
          <button class="btn btn-outline btn-sm mt-2" onclick="App.navigate('flashcards')">Ôn flashcard</button>
        </div>
        <div class="card mb-2">
          <div class="card-title">Luyện đề</div>
          <div class="card-desc">2 đề mini + 1 mock trước thi</div>
          <button class="btn btn-primary btn-sm mt-2" onclick="App.navigate('quiz')">Làm đề ngay</button>
        </div>
        <div class="card">
          <div class="card-title">AI Tutor RAG</div>
          <div class="card-desc">Hỏi từ vựng trong app — AI tra kho 1.200 từ + hội thoại</div>
          <button class="btn btn-primary btn-sm mt-2" onclick="App.navigate('ai-tutor')">Mở AI Tutor</button>
        </div>`;
    }
  },

  renderJournal() {
    const s = this.state;
    const levels = this.data.lessons?.levels || [];
    const topics = this.data.lessons?.topics || [];
    const allLessons = [];
    levels.forEach(l => (l.lessons || []).forEach(lesson => {
      allLessons.push({ ...lesson, level: l });
    }));
    const totalLessons = allLessons.length;
    const doneCount = (s.completedLessons || []).length;
    const overallPct = totalLessons ? Math.round((doneCount / totalLessons) * 100) : 0;

    document.getElementById('journalStats').innerHTML = `
      <div class="stats-row">
        <div class="stat-box"><div class="stat-icon">🔥</div><div><div class="stat-num">${s.streak}</div><div class="stat-label">Streak</div></div></div>
        <div class="stat-box"><div class="stat-icon">✅</div><div><div class="stat-num">${doneCount}</div><div class="stat-label">Bài xong</div></div></div>
        <div class="stat-box"><div class="stat-icon">📈</div><div><div class="stat-num">${overallPct}%</div><div class="stat-label">Tổng tiến độ</div></div></div>
        <div class="stat-box"><div class="stat-icon">⏱️</div><div><div class="stat-num">${s.totalStudyMinutes || 0}</div><div class="stat-label">Phút học</div></div></div>
      </div>
      <div class="card mb-2 mt-2">
        <div class="flex-between mb-1"><strong>Toàn khóa học</strong><span class="text-muted">${doneCount}/${totalLessons} bài</span></div>
        <div class="progress-track"><div class="progress-fill" style="width:${overallPct}%;background:var(--primary)"></div></div>
      </div>`;

    document.getElementById('journalHsk').innerHTML = levels.map(l => {
      const done = (s.completedLessons || []).filter(id => id.startsWith(l.id)).length;
      const total = l.lessons?.length || 1;
      const pct = Math.round((done / total) * 100);
      return `<div class="card mb-2 card-hover" role="button" onclick="App.filterHsk('${l.id}')">
        <div class="flex-between mb-1"><strong>${l.name}</strong><span style="color:${l.color};font-weight:700">${pct}%</span></div>
        <div class="progress-track"><div class="progress-fill" style="width:${pct}%;background:${l.color}"></div></div>
        <div class="text-sm text-muted mt-1">${done}/${total} bài · Nhấn để học tiếp</div>
      </div>`;
    }).join('');

    const topicEl = document.getElementById('journalTopics');
    if (topicEl) {
      topicEl.innerHTML = topics.filter(t => t.lessonCount > 0).map(t => {
        const inTopic = allLessons.filter(x => x.topic === t.id);
        const done = inTopic.filter(x => (s.completedLessons || []).includes(x.id)).length;
        const pct = inTopic.length ? Math.round((done / inTopic.length) * 100) : 0;
        return `<div class="card mb-2 card-hover" role="button" onclick="App.filterTopic('${t.id}')">
          <div class="flex-between mb-1"><span>${t.icon} ${t.name}</span><span style="font-weight:700">${pct}%</span></div>
          <div class="progress-track"><div class="progress-fill" style="width:${pct}%"></div></div>
          <div class="text-sm text-muted mt-1">${done}/${inTopic.length} bài</div>
        </div>`;
      }).join('');
    }

    const listEl = document.getElementById('journalLessonList');
    if (listEl) {
      listEl.innerHTML = allLessons.map(({ id, title, level, topic, duration }) => {
        const done = (s.completedLessons || []).includes(id);
        const prog = s.lessonProgress?.[id];
        const topicMeta = topics.find(t => t.id === topic);
        const status = done ? '✅' : prog?.startedAt ? '📖' : '○';
        return `<div class="journal-lesson-row card-hover" role="button"
          onclick="App.openLesson('${this.escAttr(id)}','${this.escAttr(level.id)}');App.navigate('lessons')">
          <span class="journal-lesson-status">${status}</span>
          <div style="flex:1">
            <div class="card-title text-sm">${title}</div>
            <div class="text-sm text-muted">${level.name}${topicMeta ? ` · ${topicMeta.name}` : ''} · ${duration} phút</div>
          </div>
        </div>`;
      }).join('');
    }

    const logEl = document.getElementById('journalStudyLog');
    if (logEl) {
      const logs = s.studyLog || [];
      logEl.innerHTML = logs.length ? logs.slice(0, 8).map(entry => {
        const when = new Date(entry.at).toLocaleString('vi-VN', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
        const label = entry.type === 'lesson' ? `Hoàn thành: ${entry.title || entry.lessonId}` : entry.type;
        return `<div class="text-sm text-muted mb-1">• ${when} — ${label}</div>`;
      }).join('') : '<p class="text-muted text-sm">Chưa có hoạt động. Hoàn thành bài đầu tiên để ghi nhận tiến độ.</p>';
    }
  },

  showUpgradeModal() {
    if (!this.requireLogin('Đăng nhập để dùng thử Premium demo')) return;
    document.getElementById('upgradeModal').classList.remove('hidden');
  },

  closeModal() {
    document.getElementById('upgradeModal')?.classList.add('hidden');
    document.getElementById('authModal')?.classList.add('hidden');
  },

  demoPremium() {
    if (!this.requireLogin('Đăng nhập để kích hoạt Premium demo')) return;
    if (this._apiOnline && HanVietAPI.token) {
      HanVietAPI.demoPremium()
        .then(() => {
          Storage.setPremium(true);
          this.state = Storage.get();
          this.setPremiumLocal(true);
          this.closeModal();
          this.renderAll();
          alert('✨ Đã kích hoạt Premium demo!');
        })
        .catch(() => this._demoPremiumLocal());
      return;
    }
    this._demoPremiumLocal();
  },

  _demoPremiumLocal() {
    Storage.setPremium(true);
    this.state = Storage.get();
    this.setPremiumLocal(true);
    this.closeModal();
    this.renderAll();
    alert('✨ Đã kích hoạt Premium demo! Bạn có thể trải nghiệm đầy đủ tính năng.');
  }
};

window.App = App;

document.addEventListener('DOMContentLoaded', () => App.init());
