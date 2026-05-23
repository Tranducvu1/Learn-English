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

  markLessonComplete(lessonId) {
    const data = this.get();
    if (!data.completedLessons.includes(lessonId)) {
      data.completedLessons.push(lessonId);
      data.wordsLearned += 5;
    }
    this.updateStudyStreak();
    this.save(data);
    return data;
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
