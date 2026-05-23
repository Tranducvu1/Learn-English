/**
 * Build thư mục dist/ để deploy GitHub Pages
 * node scripts/build-site.cjs
 */
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const root = path.join(__dirname, '..');
const dist = path.join(root, 'dist');

function rmrf(dir) {
  if (fs.existsSync(dir)) fs.rmSync(dir, { recursive: true, force: true });
}

function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const name of fs.readdirSync(src)) {
    const s = path.join(src, name);
    const d = path.join(dest, name);
    if (fs.statSync(s).isDirectory()) copyDir(s, d);
    else fs.copyFileSync(s, d);
  }
}

console.log('→ build data-bundle.js');
execSync('node scripts/build-data.cjs', { cwd: root, stdio: 'inherit' });

console.log('→ prepare dist/');
rmrf(dist);
fs.mkdirSync(dist, { recursive: true });

const copyList = ['index.html', 'css', 'js', 'data'];
for (const item of copyList) {
  const src = path.join(root, item);
  if (!fs.existsSync(src)) {
    console.warn('Skip missing:', item);
    continue;
  }
  const dest = path.join(dist, item);
  if (fs.statSync(src).isDirectory()) copyDir(src, dest);
  else fs.copyFileSync(src, dest);
}

// GitHub Pages: tắt Jekyll (tránh bỏ qua thư mục _)
fs.writeFileSync(path.join(dist, '.nojekyll'), '');

// 404 fallback → SPA
const indexHtml = fs.readFileSync(path.join(dist, 'index.html'), 'utf8');
fs.writeFileSync(path.join(dist, '404.html'), indexHtml);

const size = (() => {
  let b = 0;
  const walk = (d) => {
    for (const f of fs.readdirSync(d)) {
      const p = path.join(d, f);
      if (fs.statSync(p).isDirectory()) walk(p);
      else b += fs.statSync(p).size;
    }
  };
  walk(dist);
  return (b / 1024 / 1024).toFixed(2);
})();

console.log(`✓ dist/ ready (${size} MB)`);
console.log('  Files:', copyList.join(', '));
