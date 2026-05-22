/**
 * Voice / TTS — luyện nghe & phát âm
 */
const Voice = {
  synth: window.speechSynthesis,
  voices: [],
  rate: 0.85,
  slowRate: 0.55,
  currentIndex: 0,
  playlist: [],

  init() {
    this.loadVoices();
    if (this.synth) {
      this.synth.onvoiceschanged = () => this.loadVoices();
    }
  },

  loadVoices() {
    this.voices = this.synth?.getVoices() || [];
  },

  getChineseVoice() {
    const zh = this.voices.filter(v =>
      v.lang.startsWith('zh') || v.lang.includes('CN') || v.lang.includes('TW')
    );
    return zh.find(v => v.name.includes('Tingting')) ||
      zh.find(v => v.name.includes('Sinji')) ||
      zh.find(v => v.name.includes('Meijia')) ||
      zh[0] || null;
  },

  speak(text, options = {}) {
    if (!this.synth || !text) return Promise.resolve();
    return new Promise(resolve => {
      this.synth.cancel();
      const u = new SpeechSynthesisUtterance(text);
      u.lang = options.lang || 'zh-CN';
      u.rate = options.rate ?? this.rate;
      u.pitch = options.pitch ?? 1;
      const voice = this.getChineseVoice();
      if (voice) u.voice = voice;
      u.onend = resolve;
      u.onerror = resolve;
      this.synth.speak(u);
    });
  },

  speakSlow(text) {
    return this.speak(text, { rate: this.slowRate });
  },

  speakNormal(text) {
    return this.speak(text, { rate: 0.9 });
  },

  speakFast(text) {
    return this.speak(text, { rate: 1.1 });
  },

  /** Đọc Hán tự + pinyin */
  async speakWord(word) {
    if (!word) return;
    await this.speak(word.hanzi || word);
    if (word.pinyin) {
      await new Promise(r => setTimeout(r, 400));
      await this.speak(word.pinyin, { rate: 0.75 });
    }
  },

  setPlaylist(words) {
    this.playlist = words || [];
    this.currentIndex = 0;
  },

  async playNext() {
    if (!this.playlist.length) return null;
    const w = this.playlist[this.currentIndex];
    await this.speakWord(w);
    this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
    return w;
  },

  async playAll(delayMs = 2500) {
    for (let i = 0; i < this.playlist.length; i++) {
      this.currentIndex = i;
      await this.speakWord(this.playlist[i]);
      await new Promise(r => setTimeout(r, delayMs));
    }
  },

  stop() {
    this.synth?.cancel();
  }
};

Voice.init();
