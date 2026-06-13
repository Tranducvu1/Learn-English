/**
 * Sinh data/videos.json từ playlist YouTube HSK (yt-dlp)
 * node scripts/generate-videos.cjs
 */
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const { dataDir } = require('./paths.cjs');

const PLAYLIST_ID = 'PLWXyZU_NJb_chvMZ13hgOPB3Vcz7xhW3q';
const PLAYLIST_URL = `https://www.youtube.com/playlist?list=${PLAYLIST_ID}`;

function formatDuration(sec) {
  if (!sec) return '';
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  return `${m}:${String(s).padStart(2, '0')}`;
}

function guessLevel(title, index) {
  const t = title.toLowerCase();
  if (/hsk\s*6|level\s*6/.test(t)) return 'hsk6';
  if (/hsk\s*5|level\s*5/.test(t)) return 'hsk5';
  if (/hsk\s*4|level\s*4/.test(t)) return 'hsk4';
  if (/hsk\s*3|level\s*3/.test(t)) return 'hsk3';
  if (/hsk\s*2|level\s*2/.test(t)) return 'hsk2';
  if (index > 20) return 'hsk2';
  return 'hsk1';
}

function guessTopic(title) {
  const t = title.toLowerCase();
  if (/character|chinese character|hanzi|write/.test(t)) return 'thi-hsk';
  if (/conversation|greeting|dialogue/.test(t)) return 'giao-tiep';
  if (/number/.test(t)) return 'co-ban';
  if (/nationalit|country/.test(t)) return 'du-lich';
  return 'giao-tiep';
}

let entries = [];
try {
  const out = execSync(
    `yt-dlp --flat-playlist -j "${PLAYLIST_URL}"`,
    { encoding: 'utf8', maxBuffer: 10 * 1024 * 1024 }
  );
  entries = out.trim().split('\n').filter(Boolean).map(line => JSON.parse(line));
  console.log('Playlist:', entries.length, 'video');
} catch (e) {
  console.error('Cần yt-dlp. Cài: brew install yt-dlp');
  console.error(e.message);
  process.exit(1);
}

const videos = entries.map((e, i) => ({
  id: `vid-${String(i + 1).padStart(3, '0')}`,
  youtubeId: e.id,
  title: (e.title || `Bài ${i + 1}`).replace(/"/g, "'"),
  duration: formatDuration(e.duration),
  level: guessLevel(e.title || '', i),
  topic: guessTopic(e.title || ''),
  free: i < 24,
  hasSubtitle: true,
  tags: ['hsk', 'chineseForUs']
}));

const data = {
  featuredPlaylist: {
    id: PLAYLIST_ID,
    title: 'Khóa học HSK 1 — ChineseFor.Us (32 bài)',
    embedUrl: `https://www.youtube-nocookie.com/embed/videoseries?list=${PLAYLIST_ID}&rel=0&modestbranding=1`,
    url: PLAYLIST_URL
  },
  playlists: [
    {
      id: 'main-hsk',
      name: '📺 Khóa học HSK 1 — Toàn bộ playlist',
      source: 'youtube',
      playlistId: PLAYLIST_ID,
      playlistUrl: PLAYLIST_URL,
      description: '32 bài từ ChineseFor.Us — New HSK 1',
      premium: false,
      embedPlaylist: true,
      videos: []
    },
    {
      id: 'hsk-lessons',
      name: 'Bài giảng từng video',
      source: 'youtube',
      premium: false,
      videos
    }
  ],
  channels: [
    { name: 'ChineseFor.Us', url: 'https://www.youtube.com/@ChineseForUsOfficial' },
    { name: 'Playlist HSK 1', url: PLAYLIST_URL }
  ]
};

const outPath = path.join(dataDir, 'videos.json');
fs.writeFileSync(outPath, JSON.stringify(data, null, 2));
console.log('OK', outPath, '—', videos.length, 'video');
