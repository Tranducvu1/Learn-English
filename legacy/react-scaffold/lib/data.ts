import type { AppData } from '../types/data';

/** Tải JSON từ thư mục data/ — giữ nguyên nguồn dữ liệu */
export async function loadAppData(): Promise<AppData> {
  const [lessons, vocabulary, quizzes, dictionary, videos, premium] = await Promise.all([
    import('../../data/lessons.json').then(m => m.default),
    import('../../data/vocabulary.json').then(m => m.default),
    import('../../data/quizzes.json').then(m => m.default),
    import('../../data/dictionary.json').then(m => m.default),
    import('../../data/videos.json').then(m => m.default),
    import('../../data/premium.json').then(m => m.default),
  ]);
  return { lessons, vocabulary, quizzes, dictionary, videos, premium } as unknown as AppData;
}

export function buildVocabMap(words: AppData['vocabulary']['words']) {
  const map = new Map<string, (typeof words)[0]>();
  words.forEach(w => {
    map.set(w.id, w);
    map.set(w.hanzi, w);
    const m = w.id.match(/^v0*(\d+)$/i);
    if (m) {
      map.set(`v${m[1]}`, w);
      map.set(`v${String(parseInt(m[1], 10)).padStart(3, '0')}`, w);
      map.set(`v${String(parseInt(m[1], 10)).padStart(4, '0')}`, w);
    }
  });
  return map;
}

export function resolveWord(map: ReturnType<typeof buildVocabMap>, key: string) {
  if (map.has(key)) return map.get(key)!;
  const m = key.match(/^v(\d+)$/i);
  if (m) {
    const padded = `v${String(parseInt(m[1], 10)).padStart(4, '0')}`;
    if (map.has(padded)) return map.get(padded)!;
  }
  return undefined;
}

export function youtubeEmbedUrl(videoId: string, list?: string) {
  const params = new URLSearchParams({ rel: '0', modestbranding: '1', playsinline: '1' });
  if (list) params.set('list', list);
  const origin = typeof window !== 'undefined' && window.location.origin !== 'null'
    ? window.location.origin : '';
  if (origin) params.set('origin', origin);
  return `https://www.youtube-nocookie.com/embed/${videoId}?${params}`;
}
