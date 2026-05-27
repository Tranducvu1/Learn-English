import type { UserState } from '../types/data';

const KEY = 'hanviet_learn';

function defaultState(): UserState {
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
    studyLog: [],
  };
}

export function getState(): UserState {
  try {
    return JSON.parse(localStorage.getItem(KEY) || '') || defaultState();
  } catch {
    return defaultState();
  }
}

export function saveState(data: UserState) {
  localStorage.setItem(KEY, JSON.stringify(data));
}

export function updateState(patch: Partial<UserState>): UserState {
  const data = { ...getState(), ...patch };
  saveState(data);
  return data;
}

function touchHskProgress(data: UserState, levelId: string, totalLessons?: number) {
  if (!levelId.startsWith('hsk')) return;
  const done = data.completedLessons.filter(id => id.startsWith(levelId)).length;
  const total = totalLessons || 1;
  data.hskProgress[levelId] = Math.min(100, Math.round((done / total) * 100));
}

export function updateStudyStreak(data: UserState): UserState {
  const today = new Date().toDateString();
  if (data.lastStudyDate === today) return data;
  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);
  const wasYesterday = data.lastStudyDate === yesterday.toDateString();
  data.streak = wasYesterday || !data.lastStudyDate ? (data.streak || 0) + 1 : 1;
  data.lastStudyDate = today;
  data.totalStudyMinutes += 10;
  saveState(data);
  return data;
}

export function markLessonOpened(lessonId: string, levelId: string): UserState {
  const data = getState();
  const prev = data.lessonProgress[lessonId] || {};
  data.lessonProgress[lessonId] = {
    ...prev,
    levelId: levelId || prev.levelId,
    startedAt: prev.startedAt || Date.now(),
    lastOpenedAt: Date.now(),
  };
  saveState(data);
  return data;
}

export function markLessonComplete(
  lessonId: string,
  levelId: string,
  title: string,
  totalByLevel?: Record<string, number>
): UserState {
  const data = getState();
  if (!data.completedLessons.includes(lessonId)) {
    data.completedLessons.push(lessonId);
    data.wordsLearned += 5;
  }
  data.lessonProgress[lessonId] = {
    ...(data.lessonProgress[lessonId] || {}),
    levelId,
    completed: true,
    completedAt: Date.now(),
  };
  if (levelId) touchHskProgress(data, levelId, totalByLevel?.[levelId]);
  data.studyLog = [
    { type: 'lesson', lessonId, levelId, title, at: Date.now() },
    ...data.studyLog,
  ].slice(0, 40);
  updateStudyStreak(data);
  saveState(data);
  return data;
}

export function recalcHskProgress(
  levels: { id: string; lessons?: unknown[] }[]
): UserState {
  const data = getState();
  levels.forEach(l => touchHskProgress(data, l.id, l.lessons?.length));
  saveState(data);
  return data;
}

export function setSetting<K extends keyof UserState['settings']>(
  key: K,
  value: UserState['settings'][K]
): UserState {
  const data = getState();
  data.settings = { ...data.settings, [key]: value };
  saveState(data);
  return data;
}

export function setPremium(value: boolean): UserState {
  return updateState({ isPremium: value });
}
