import type { Word } from '../types/data';

class VoiceService {
  synth = typeof window !== 'undefined' ? window.speechSynthesis : null;
  voices: SpeechSynthesisVoice[] = [];
  currentAudio: HTMLAudioElement | null = null;
  engine: 'youdao' | 'browser' = 'youdao';
  rate = 0.92;
  slowRate = 0.72;
  playlist: Word[] = [];
  currentIndex = 0;
  private ready: Promise<void>;

  constructor() {
    const saved = localStorage.getItem('hanviet_tts');
    if (saved === 'browser' || saved === 'youdao') this.engine = saved;
    this.ready = new Promise(resolve => {
      const done = () => { this.loadVoices(); resolve(); };
      if (!this.synth) { resolve(); return; }
      done();
      this.synth.onvoiceschanged = done;
      setTimeout(done, 500);
    });
  }

  loadVoices() {
    this.voices = this.synth?.getVoices() || [];
  }

  toHanzi(text: string) {
    if (!text) return '';
    const han = String(text).trim().match(/[\u4e00-\u9fff]+/g);
    return han?.join('') || '';
  }

  getChineseVoice() {
    const zh = this.voices.filter(v => {
      const l = (v.lang || '').toLowerCase();
      return l.startsWith('zh') || l.includes('cmn');
    });
    const prefer = ['Tingting', 'Sinji', 'Meijia', 'Google', 'Microsoft', 'Xiaoxiao'];
    for (const name of prefer) {
      const v = zh.find(x => x.name.includes(name));
      if (v) return v;
    }
    return zh.find(v => v.lang.startsWith('zh-CN')) || zh[0] || null;
  }

  stop() {
    this.synth?.cancel();
    if (this.currentAudio) {
      this.currentAudio.pause();
      this.currentAudio.src = '';
      this.currentAudio = null;
    }
  }

  playYoudao(hanzi: string) {
    return new Promise<void>((resolve, reject) => {
      this.stop();
      const audio = new Audio();
      this.currentAudio = audio;
      audio.src = `https://dict.youdao.com/dictvoice?audio=${encodeURIComponent(hanzi)}&type=2`;
      audio.onended = () => { this.currentAudio = null; resolve(); };
      audio.onerror = () => { this.currentAudio = null; reject(new Error('youdao')); };
      audio.play().catch(reject);
    });
  }

  speakLocal(hanzi: string, rate = this.rate) {
    if (!this.synth) return Promise.resolve();
    return new Promise<void>(resolve => {
      this.synth!.cancel();
      const u = new SpeechSynthesisUtterance(hanzi);
      u.lang = 'zh-CN';
      u.rate = rate;
      const voice = this.getChineseVoice();
      if (voice) { u.voice = voice; u.lang = voice.lang || 'zh-CN'; }
      u.onend = () => resolve();
      u.onerror = () => resolve();
      this.synth!.speak(u);
    });
  }

  async speak(hanziOrText: string, opts: { rate?: number; forceBrowser?: boolean } = {}) {
    const hanzi = this.toHanzi(hanziOrText) || (/[\u4e00-\u9fff]/.test(hanziOrText) ? hanziOrText : '');
    if (!hanzi) return;
    await this.ready;
    if (this.engine !== 'browser' && !opts.forceBrowser) {
      try {
        await this.playYoudao(hanzi);
        return;
      } catch { /* fallback */ }
    }
    await this.speakLocal(hanzi, opts.rate ?? this.rate);
  }

  async speakWord(word: Word | string) {
    const hanzi = typeof word === 'string' ? word : word.hanzi;
    if (hanzi) await this.speak(hanzi);
  }

  setEngine(engine: 'youdao' | 'browser') {
    this.engine = engine;
    localStorage.setItem('hanviet_tts', engine);
  }

  setPlaylist(words: Word[]) {
    this.playlist = words;
    this.currentIndex = 0;
  }
}

export const voice = new VoiceService();
