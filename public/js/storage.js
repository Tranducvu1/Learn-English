/**
 * LocalStorage — tiến độ, streak, SRS, premium
 */
const Storage = {
  KEY: 'hanviet_learn',

  get() {
    try {
      return JSON.parse(localStorage.getItem(this.KEY)) || this.default();
    } catch {
      return this.default();
    }
  },

  default() {
    return {
      isPremium: false,
      streak: 0,
      lastStudyDate: null,
      totalStudyMinutes: 0,
      wordsLearned: 0,
      completedLessons: [],
      lessonProgress: {},
      hskProgress: { hsk1: 0, hsk2: 0, hsk3: 0, hsk4: 0, hsk5: 0, hsk6: 0 },
      srsCards: {},
      quizScores: {},
      settings: { darkMode: false, showPinyin: true, fontSize: 'medium' },
      studyLog: []
    };
  },

  save(data) {
    localStorage.setItem(this.KEY, JSON.stringify(data));
  },

  update(patch) {
    const data = { ...this.get(), ...patch };
    this.save(data);
    return data;
  },

  markLessonOpened(lessonId, levelId) {
    const data = this.get();
    const prev = data.lessonProgress[lessonId] || {};
    data.lessonProgress[lessonId] = {
      ...prev,
      levelId: levelId || prev.levelId,
      startedAt: prev.startedAt || Date.now(),
      lastOpenedAt: Date.now()
    };
    this.save(data);
    return data;
  },

  markLessonComplete(lessonId, levelId, title) {
    const data = this.get();
    const wasNew = !data.completedLessons.includes(lessonId);
    if (wasNew) {
      data.completedLessons.push(lessonId);
      data.wordsLearned += 5;
    }
    data.lessonProgress[lessonId] = {
      ...(data.lessonProgress[lessonId] || {}),
      levelId: levelId || data.lessonProgress[lessonId]?.levelId,
      completed: true,
      completedAt: Date.now()
    };
    if (levelId) this._touchHskProgress(data, levelId);
    data.studyLog = [
      { type: 'lesson', lessonId, levelId, title: title || lessonId, at: Date.now() },
      ...(data.studyLog || [])
    ].slice(0, 40);
    this.updateStudyStreak();
    this.save(data);
    return data;
  },

  recalcHskProgress(levels) {
    const data = this.get();
    (levels || []).forEach(l => this._touchHskProgress(data, l.id, l.lessons?.length));
    this.save(data);
    return data;
  },

  _touchHskProgress(data, levelId, totalLessons) {
    if (!levelId || !levelId.startsWith('hsk')) return;
    const done = (data.completedLessons || []).filter(id => id.startsWith(levelId)).length;
    const total = totalLessons || 1;
    data.hskProgress[levelId] = Math.min(100, Math.round((done / total) * 100));
  },

  updateStudyStreak() {
    const data = this.get();
    const today = new Date().toDateString();
    const last = data.lastStudyDate;

    if (last === today) return data;

    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const wasYesterday = last === yesterday.toDateString();

    data.streak = wasYesterday || !last ? (data.streak || 0) + 1 : 1;
    data.lastStudyDate = today;
    data.totalStudyMinutes += 10;
    this.save(data);
    return data;
  },

  addStudyMinutes(mins) {
    const data = this.get();
    data.totalStudyMinutes += mins;
    this.save(data);
  },

  setPremium(value) {
    return this.update({ isPremium: value });
  },

  getSetting(key) {
    return this.get().settings?.[key];
  },

  setSetting(key, value) {
    const data = this.get();
    data.settings = { ...data.settings, [key]: value };
    this.save(data);
  }
};
