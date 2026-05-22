/**
 * Nhúng toàn bộ JSON vào js/data-bundle.js — chạy được cả file://
 * node scripts/build-data.cjs
 */
const fs = require('fs');
const path = require('path');
const root = path.join(__dirname, '..');

const files = ['lessons', 'vocabulary', 'quizzes', 'dictionary', 'videos', 'premium'];
let out = '/* Auto-generated — npm run data:build */\nwindow.APP_DATA = {};\n';

for (const f of files) {
  const p = path.join(root, 'data', f + '.json');
  const j = JSON.parse(fs.readFileSync(p, 'utf8'));
  out += `window.APP_DATA.${f} = ${JSON.stringify(j)};\n`;
}

const outPath = path.join(root, 'js', 'data-bundle.js');
fs.writeFileSync(outPath, out);
const size = (fs.statSync(outPath).size / 1024).toFixed(0);
console.log('OK', outPath, size + ' KB');
console.log('vocabulary:', JSON.parse(fs.readFileSync(path.join(root, 'data/vocabulary.json'))).words?.length);
console.log('quizzes:', JSON.parse(fs.readFileSync(path.join(root, 'data/quizzes.json'))).quizzes?.length);
