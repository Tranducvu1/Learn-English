/**
 * Voice — phát âm tiếng Trung chuẩn
 * 1) Youdao TTS (Hán tự, giọng bản xứ) — ưu tiên
 * 2) Web Speech API + giọng zh-CN/zh-TW — dự phòng
 */
const Voice = {
  synth: window.speechSynthesis,
  voices: [],
  voicesReady: null,
  currentAudio: null,
  engine: 'youdao', // 'youdao' | 'browser' — đổi trong localStorage key hanviet_tts
  rate: 0.92,
  slowRate: 0.72,
  playlist: [],
  currentIndex: 0,

  init() {
    const saved = localStorage.getItem('hanviet_tts');
    if (saved === 'browser' || saved === 'youdao') this.engine = saved;

    this.voicesReady = new Promise(resolve => {
      const done = () => {
        this.loadVoices();
        resolve();
      };
      if (!this.synth) {
        resolve();
        return;
      }
      done();
      this.synth.onvoiceschanged = done;
      setTimeout(done, 500);
    });
  },

  loadVoices() {
    this.voices = this.synth?.getVoices() || [];
  },

  /** Chỉ lấy chữ Hán — không đọc pinyin */
  toHanzi(text) {
    if (!text) return '';
    const s = String(text).trim();
    const han = s.match(/[\u4e00-\u9fff]+/g);
    if (han && han.join('').length >= 1) return han.join('');
    return '';
  },

  isHanzi(text) {
    return /[\u4e00-\u9fff]/.test(String(text || ''));
  },

  getChineseVoice() {
    const zh = this.voices.filter(v => {
      const l = (v.lang || '').toLowerCase();
      return l.startsWith('zh') || l.includes('cmn') || l.includes('yue');
    });
    const prefer = [
      'Tingting', 'Sinji', 'Meijia', 'Ting-Ting', 'Yu-shu',
      'Google', 'Microsoft', 'Xiaoxiao', 'Yunxi', 'Hanhan',
      'Lili', 'Huihui', 'Kangkang', 'Yaoyao', 'Tracy'
    ];
    for (const name of prefer) {
      const v = zh.find(x => x.name.includes(name));
      if (v) return v;
    }
    const cn = zh.find(v => v.lang === 'zh-CN' || v.lang.startsWith('zh-CN'));
    return cn || zh.find(v => v.lang.startsWith('zh-TW')) || zh[0] || null;
  },

  stop() {
    this.synth?.cancel();
    if (this.currentAudio) {
      this.currentAudio.pause();
      this.currentAudio.src = '';
      this.currentAudio = null;
    }
  },

  /** Youdao — phát âm tiếng Trung chuẩn (dùng Audio, không cần fetch CORS) */
  playYoudao(hanzi) {
    return new Promise((resolve, reject) => {
      this.stop();
      const audio = new Audio();
      this.currentAudio = audio;
      const q = encodeURIComponent(hanzi);
      // type=2: giọp đọc tiếng Trung (phổ thông)
      audio.src = `https://dict.youdao.com/dictvoice?audio=${q}&type=2`;
      audio.onended = () => { this.currentAudio = null; resolve(); };
      audio.onerror = () => { this.currentAudio = null; reject(new Error('youdao')); };
      audio.play().catch(reject);
    });
  },

  speakLocal(hanzi, options = {}) {
    if (!this.synth) return Promise.resolve();
    return new Promise(resolve => {
      this.synth.cancel();
      const u = new SpeechSynthesisUtterance(hanzi);
      u.lang = 'zh-CN';
      u.rate = options.rate ?? this.rate;
      u.pitch = 1;
      const voice = this.getChineseVoice();
      if (voice) {
        u.voice = voice;
        u.lang = voice.lang || 'zh-CN';
      }
      u.onend = resolve;
      u.onerror = resolve;
      this.synth.speak(u);
    });
  },

  async speak(hanziOrText, options = {}) {
    const hanzi = this.toHanzi(hanziOrText) || (this.isHanzi(hanziOrText) ? hanziOrText : '');
    if (!hanzi) return;

    await this.voicesReady;

    const useBrowser = this.engine === 'browser' || options.forceBrowser;

    if (!useBrowser) {
      try {
        await this.playYoudao(hanzi);
        return;
      } catch (e) {
        console.warn('Youdao TTS fallback:', e.message);
      }
    }
    await this.speakLocal(hanzi, options);
  },

  speakSlow(hanzi) {
    if (this.engine === 'browser') {
      return this.speak(hanzi, { rate: this.slowRate, forceBrowser: true });
    }
    return this.playYoudao(hanzi).catch(() => this.speakLocal(hanzi, { rate: this.slowRate }));
  },

  speakNormal(hanzi) {
    return this.speak(hanzi, { rate: 0.95 });
  },

  /** Chỉ đọc Hán tự — KHÔNG đọc pinyin (pinyin đọc bằng TTS rất sai) */
  async speakWord(word) {
    if (!word) return;
    const hanzi = word.hanzi || this.toHanzi(word);
    if (!hanzi) return;
    await this.speak(hanzi);
  },

  setEngine(engine) {
    if (engine === 'youdao' || engine === 'browser') {
      this.engine = engine;
      localStorage.setItem('hanviet_tts', engine);
    }
  },

  setPlaylist(words) {
    this.playlist = words || [];
    this.currentIndex = 0;
  },

  async playAll(delayMs = 2800) {
    for (let i = 0; i < this.playlist.length; i++) {
      this.currentIndex = i;
      await this.speakWord(this.playlist[i]);
      await new Promise(r => setTimeout(r, delayMs));
    }
  }
};

Voice.init();
