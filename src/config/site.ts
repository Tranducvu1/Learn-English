/** Cấu hình site — URL dùng cho SEO, sitemap, canonical */
export const SITE = {
  name: '汉越学堂',
  nameFull: '汉越学堂 — Học Tiếng Trung & Luyện Thi HSK Online',
  tagline: 'Nền tảng học tiếng Trung miễn phí: bài học HSK 1-6, 1200+ từ vựng, flashcard SRS, luyện đề thi thử',
  url: import.meta.env.VITE_SITE_URL || 'https://tranducvu1.github.io/Learn-English',
  locale: 'vi_VN',
  lang: 'vi',
  author: '汉越学堂',
  twitter: '@hanviet',
} as const;

/** Từ khóa chính — học tiếng Trung / HSK (Search Intent: thông tin + giao dịch) */
export const PRIMARY_KEYWORDS = [
  'học tiếng trung online',
  'luyện thi HSK',
  'HSK 1', 'HSK 2', 'HSK 3', 'HSK 4', 'HSK 5', 'HSK 6',
  'từ vựng tiếng trung',
  'học tiếng trung miễn phí',
  'thi thử HSK',
  'flashcard tiếng trung',
  'bài học tiếng trung',
  'luyện nghe tiếng trung',
  'pinyin tiếng trung',
  'chữ Hán',
  '汉语', '中文', 'HSK考试',
] as const;
