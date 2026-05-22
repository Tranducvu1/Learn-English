/**
 * Tạo lại data/vocabulary.json từ HSK (1200 từ)
 * node scripts/generate-vocabulary.cjs
 */
const fs = require('fs');
const https = require('https');
const path = require('path');

const LEVELS = [
  ['new', 1], ['new', 2], ['new', 3], ['new', 4],
  ['old', 1], ['old', 2], ['old', 3], ['old', 4]
];

function fetchJson(url) {
  return new Promise((resolve, reject) => {
    https.get(url, res => {
      let d = '';
      res.on('data', c => d += c);
      res.on('end', () => {
        try { resolve(JSON.parse(d)); } catch (e) { reject(e); }
      });
    }).on('error', reject);
  });
}

function hskNum(levelArr) {
  const m = { 'new-1': 1, 'old-1': 1, 'new-2': 2, 'old-2': 2, 'new-3': 3, 'old-3': 3, 'new-4': 4, 'old-4': 4 };
  let min = 99;
  for (const l of levelArr || []) {
    if (m[l] && m[l] < min) min = m[l];
  }
  return min === 99 ? 1 : min;
}

function topicForHsk(h) {
  if (h <= 1) return 'giao-tiep';
  if (h === 2) return 'co-ban';
  if (h === 3) return 'mua-sam';
  return 'cong-viec';
}

(async () => {
  const seen = new Map();
  let idx = 0;
  for (const [ver, lvl] of LEVELS) {
    const url = `https://raw.githubusercontent.com/drkameleon/complete-hsk-vocabulary/main/wordlists/inclusive/${ver}/${lvl}.json`;
    try {
      const data = await fetchJson(url);
      for (const item of data) {
        const hanzi = item.simplified;
        if (!hanzi || seen.has(hanzi)) continue;
        const form = item.forms?.[0];
        if (!form) continue;
        const pinyin = form.transcriptions?.pinyin || '';
        const en = (form.meanings?.[0] || '').split(';')[0].trim();
        const hsk = Math.min(hskNum(item.level), 6);
        idx++;
        seen.set(hanzi, { hanzi, pinyin, vietnamese: en, english: en, hsk, topic: topicForHsk(hsk) });
      }
    } catch (e) {
      console.warn(ver, lvl, e.message);
    }
  }
  let words = [...seen.values()].slice(0, 1200);
  words.sort((a, b) => a.hsk - b.hsk || a.hanzi.localeCompare(b.hanzi));
  words = words.map((w, i) => ({
    id: `v${String(i + 1).padStart(4, '0')}`,
    ...w,
    example: { hanzi: w.hanzi, pinyin: w.pinyin, vietnamese: w.vietnamese }
  }));
  const out = path.join(__dirname, '..', 'data', 'vocabulary.json');
  fs.writeFileSync(out, JSON.stringify({ meta: { count: words.length }, words }, null, 0));
  console.log('OK', words.length, 'words ->', out);
})();
