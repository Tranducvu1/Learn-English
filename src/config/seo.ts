import { PRIMARY_KEYWORDS, SITE } from './site';

export type RouteSeo = {
  path: string;
  title: string;
  description: string;
  keywords: string[];
  h1: string;
  /** Breadcrumb / JSON-LD */
  breadcrumb: string;
};

/** On-page SEO theo từng URL — title ≤60 ký tự, description ≤160 ký tự (Google hiển thị) */
export const ROUTE_SEO: Record<string, RouteSeo> = {
  home: {
    path: '/',
    title: 'Học Tiếng Trung Online Miễn Phí | Luyện Thi HSK 1-6 — 汉越学堂',
    description: 'Học tiếng Trung online miễn phí: 214+ bài HSK, 1200 từ vựng, flashcard SRS, luyện đề thi thử, video bài giảng. Phù hợp người Việt luyện thi HSK.',
    keywords: [...PRIMARY_KEYWORDS],
    h1: 'Học tiếng Trung & luyện thi HSK online miễn phí',
    breadcrumb: 'Trang chủ',
  },
  lessons: {
    path: '/luyen-hsk',
    title: 'Luyện Thi HSK 1-6 | Bài Học Tiếng Trung Theo Chủ Đề',
    description: '214 bài học HSK 1 đến HSK 6: giao tiếp, phương tiện, ăn uống, du lịch, công việc. Hội thoại, từ vựng và luyện 4 kỹ năng.',
    keywords: ['luyện thi HSK', 'bài học tiếng trung', 'HSK 1', 'HSK 2', 'giao tiếp tiếng trung', '汉语', '中文学习'],
    h1: 'Luyện thi HSK & bài học tiếng Trung',
    breadcrumb: 'Luyện HSK',
  },
  vocabulary: {
    path: '/tu-vung-hsk',
    title: '1200+ Từ Vựng HSK 1-6 | Kho Từ Tiếng Trung Có Pinyin',
    description: 'Tra cứu 1200 từ vựng HSK có pinyin, nghĩa tiếng Việt, phát âm chuẩn. Lọc theo cấp HSK và 20 chủ đề: phương tiện, ăn uống, du lịch.',
    keywords: ['từ vựng HSK', 'từ vựng tiếng trung', 'HSK vocabulary', 'pinyin', 'chữ Hán', '汉语词汇'],
    h1: 'Kho từ vựng HSK 1-6',
    breadcrumb: 'Từ vựng',
  },
  videos: {
    path: '/video-bai-giang-hsk',
    title: 'Video Bài Giảng HSK 1 | Khóa ChineseFor.Us Miễn Phí',
    description: '32 video bài giảng HSK 1 từ playlist ChineseFor.Us: phát âm, chữ Hán, hội thoại, luyện nghe. Xem nhúng hoặc trên YouTube.',
    keywords: ['video học tiếng trung', 'bài giảng HSK', 'ChineseFor.Us', 'luyện nghe tiếng trung', 'HSK 1 video'],
    h1: 'Video bài giảng HSK',
    breadcrumb: 'Video',
  },
  quiz: {
    path: '/luyen-de-hsk',
    title: 'Luyện Đề Thi Thử HSK | Quiz Tiếng Trung Có Giải Thích',
    description: '86 đề quiz luyện thi HSK với giải thích đáp án. Ôn từ vựng, ngữ pháp và mô phỏng format đề thi HSK thật.',
    keywords: ['luyện đề HSK', 'thi thử HSK', 'quiz tiếng trung', 'HSK mock test', 'HSK考试'],
    h1: 'Luyện đề thi thử HSK',
    breadcrumb: 'Luyện đề',
  },
  flashcards: {
    path: '/flashcard-tieng-trung',
    title: 'Flashcard Tiếng Trung SRS | Ôn Từ Vựng HSK Thông Minh',
    description: 'Flashcard SRS (Spaced Repetition): ôn từ vựng HSK theo lịch thông minh, tăng ghi nhớ lâu dài. Miễn phí, không cần cài app.',
    keywords: ['flashcard tiếng trung', 'SRS', 'ôn từ vựng HSK', 'spaced repetition', '汉语卡片'],
    h1: 'Flashcard SRS — ôn từ vựng HSK',
    breadcrumb: 'Flashcard',
  },
  voice: {
    path: '/luyen-giong-noi',
    title: 'Luyện Phát Âm Tiếng Trung | TTS Hán Tự Chuẩn',
    description: 'Luyện phát âm tiếng Trung với TTS Youdao và giọng bản xổ. Nghe từng từ HSK, luyện theo cấp độ HSK 1-6.',
    keywords: ['phát âm tiếng trung', 'luyện giọng tiếng trung', 'pinyin', 'TTS tiếng trung', '汉语发音'],
    h1: 'Luyện phát âm tiếng Trung',
    breadcrumb: 'Luyện giọng',
  },
  dictionary: {
    path: '/tu-dien-tieng-trung',
    title: 'Từ Điển Tiếng Trung Trực Tuyến | Hán-Việt Pinyin',
    description: 'Từ điển tiếng Trung online: tra Hán tự, pinyin, nghĩa tiếng Việt. Hỗ trợ học HSK và giao tiếp hàng ngày.',
    keywords: ['từ điển tiếng trung', 'Hán Việt', 'tra cứu chữ Hán', 'pinyin dictionary', '汉语词典'],
    h1: 'Từ điển tiếng Trung',
    breadcrumb: 'Từ điển',
  },
  journal: {
    path: '/tien-do-hoc-tap',
    title: 'Tiến Độ Học Tiếng Trung | Theo Dõi HSK & Streak',
    description: 'Theo dõi tiến độ học HSK: bài đã hoàn thành, streak ngày, tiến độ theo cấp HSK và chủ đề. Nhật ký học tập.',
    keywords: ['tiến độ học tiếng trung', 'theo dõi HSK', 'streak học tập', 'learning progress Chinese'],
    h1: 'Tiến độ học tập',
    breadcrumb: 'Tiến độ',
  },
  premium: {
    path: '/premium',
    title: 'Premium Học Tiếng Trung | AI Tutor & Lộ Trình Cá Nhân',
    description: 'Nâng cấp Premium: AI Tutor, phát âm Pro, lộ trình học cá nhân, nội dung độc quyền. Học tiếng Trung nhanh hơn 3 lần.',
    keywords: ['học tiếng trung premium', 'AI tutor tiếng trung', 'lộ trình HSK'],
    h1: 'Nâng cấp Premium',
    breadcrumb: 'Premium',
  },
};

export function getSeoByPath(pathname: string): RouteSeo {
  const entry = Object.values(ROUTE_SEO).find(r => r.path === pathname);
  return entry || ROUTE_SEO.home;
}

export function canonicalUrl(path: string): string {
  const base = SITE.url.replace(/\/$/, '');
  const p = path.startsWith('/') ? path : `/${path}`;
  return p === '/' ? `${base}/` : `${base}${p}`;
}
