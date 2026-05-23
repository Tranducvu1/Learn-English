/**
 * Build dist/ cho GitHub Pages
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

execSync('node scripts/build-data.cjs', { cwd: root, stdio: 'inherit' });

rmrf(dist);
fs.mkdirSync(dist, { recursive: true });

for (const item of ['index.html', 'css', 'js', 'data']) {
  const src = path.join(root, item);
  if (!fs.existsSync(src)) continue;
  const dest = path.join(dist, item);
  if (fs.statSync(src).isDirectory()) copyDir(src, dest);
  else fs.copyFileSync(src, dest);
}

fs.writeFileSync(path.join(dist, '.nojekyll'), '');
fs.writeFileSync(path.join(dist, '404.html'), fs.readFileSync(path.join(dist, 'index.html'), 'utf8'));
console.log('✓ dist/ ready');
