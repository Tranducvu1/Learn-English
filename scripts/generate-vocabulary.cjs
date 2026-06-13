/**
 * Tạo lại data/vocabulary.json từ HSK 1-6 (1200 từ, ~200/cấp)
 * node scripts/generate-vocabulary.cjs
 */
const fs = require('fs');
const https = require('https');
const path = require('path');
const { classifyTopic } = require('./topic-rules.cjs');
const { dataDir } = require('./paths.cjs');

const PER_LEVEL = 200;
const LEVELS = [1, 2, 3, 4, 5, 6];

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

(async () => {
  const globalSeen = new Set();
  const byLevel = {};

  for (const lvl of LEVELS) {
    const url = `https://raw.githubusercontent.com/drkameleon/complete-hsk-vocabulary/main/wordlists/inclusive/new/${lvl}.json`;
    byLevel[lvl] = [];
    try {
      const data = await fetchJson(url);
      for (const item of data) {
        if (byLevel[lvl].length >= PER_LEVEL) break;
        const hanzi = item.simplified;
        if (!hanzi || globalSeen.has(hanzi)) continue;
        const form = item.forms?.[0];
        if (!form) continue;
        const pinyin = form.transcriptions?.pinyin || '';
        const en = (form.meanings?.[0] || '').split(';')[0].trim();
        globalSeen.add(hanzi);
        byLevel[lvl].push({
          hanzi, pinyin, vietnamese: en, english: en, hsk: lvl,
          topic: classifyTopic(hanzi, pinyin, en, lvl)
        });
      }
      console.log(`HSK ${lvl}: ${byLevel[lvl].length} từ`);
    } catch (e) {
      console.warn('HSK', lvl, e.message);
    }
  }

  let words = LEVELS.flatMap(l => byLevel[l]);
  words.sort((a, b) => a.hsk - b.hsk || a.hanzi.localeCompare(b.hanzi));
  words = words.map((w, i) => ({
    id: `v${String(i + 1).padStart(4, '0')}`,
    ...w,
    example: { hanzi: w.hanzi, pinyin: w.pinyin, vietnamese: w.vietnamese }
  }));

  const out = path.join(dataDir, 'vocabulary.json');
  fs.writeFileSync(out, JSON.stringify({ meta: { count: words.length, perLevel: PER_LEVEL }, words }, null, 0));
  console.log('OK', words.length, 'words ->', out);

  const topicCount = {};
  words.forEach(w => { topicCount[w.topic] = (topicCount[w.topic] || 0) + 1; });
  Object.entries(topicCount).sort((a, b) => b[1] - a[1]).forEach(([t, n]) => console.log(`  ${t}: ${n} từ`));
})();
