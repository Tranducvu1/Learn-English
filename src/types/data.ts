export type Word = {
  id: string;
  hanzi: string;
  pinyin: string;
  vietnamese: string;
  english?: string;
  hsk: number;
  topic?: string;
  example?: { hanzi: string; pinyin?: string; vietnamese?: string };
};

export type Lesson = {
  id: string;
  title: string;
  topic: string;
  duration: number;
  vocabIds: string[];
  skills: string[];
  content?: {
    intro?: string;
    dialogue?: { speaker: string; hanzi: string; pinyin: string; vietnamese: string }[];
  };
};

export type Level = {
  id: string;
  name: string;
  color: string;
  description: string;
  lessons: Lesson[];
  totalLessons?: number;
};

export type Topic = { id: string; name: string; icon: string; lessonCount?: number };

export type Quiz = {
  id: string;
  title: string;
  level: string;
  questions: {
    id: string;
    type: string;
    question: string;
    options: string[];
    correctIndex: number;
    explanation?: string;
    audioText?: string;
  }[];
};

export type VideoItem = {
  id: string;
  youtubeId: string;
  title: string;
  duration?: string;
  level?: string;
  topic?: string;
  free?: boolean;
};

export type AppData = {
  lessons: { levels: Level[]; topics: Topic[] };
  vocabulary: { words: Word[]; meta?: { count: number } };
  quizzes: { quizzes: Quiz[] };
  dictionary: { entries: { hanzi: string; pinyin: string; vietnamese: string }[] };
  videos: {
    featuredPlaylist?: { id: string; title: string; embedUrl: string; url: string };
    playlists: { id: string; name: string; videos: VideoItem[]; embedPlaylist?: boolean; premium?: boolean }[];
  };
  premium: {
    pricing: { monthly: { label: string }; yearly: { label: string; savings?: string } };
    features: { id: string; title: string; description: string; highlights: string[] }[];
  };
};

export type UserState = {
  isPremium: boolean;
  streak: number;
  lastStudyDate: string | null;
  totalStudyMinutes: number;
  wordsLearned: number;
  completedLessons: string[];
  lessonProgress: Record<string, { levelId?: string; startedAt?: number; completed?: boolean; completedAt?: number }>;
  hskProgress: Record<string, number>;
  srsCards: Record<string, SrsCard>;
  quizScores: Record<string, number>;
  settings: { darkMode: boolean; showPinyin: boolean; fontSize: string };
  studyLog: { type: string; lessonId?: string; levelId?: string; title?: string; at: number }[];
};

export type SrsCard = {
  wordId: string;
  ease: number;
  interval: number;
  repetitions: number;
  nextReview: number;
  lastReview: number | null;
};
