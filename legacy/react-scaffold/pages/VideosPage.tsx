import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { PageShell } from '../components/ui/PageShell';
import { youtubeEmbedUrl } from '../lib/data';
import type { VideoItem } from '../types/data';

type Playing = { id: string; title: string } | null;

function youtubeWatchUrl(videoId: string) {
  return `https://www.youtube.com/watch?v=${videoId}`;
}

export function VideosPage() {
  const { data, isPremium } = useApp();
  const [playing, setPlaying] = useState<Playing>(null);

  if (!data) return null;

  const vdata = data.videos;
  const fp = vdata.featuredPlaylist;
  const plEmbed = fp ? youtubeEmbedUrl('videoseries', fp.id) : '';

  const playVideo = (v: VideoItem) => {
    const id = v.youtubeId || '';
    const invalid = !id || id.startsWith('PLACEHOLDER');
    const locked = (!v.free && !isPremium) || invalid;
    if (locked) {
      if (invalid) alert('Video không khả dụng. Vui lòng chọn video khác.');
      else alert('Video Premium — nâng cấp để xem.');
      return;
    }
    setPlaying({ id, title: v.title });
  };

  if (playing) {
    const embedSrc = youtubeEmbedUrl(playing.id);
    return (
      <PageShell title={playing.title} desc="Video bài giảng HSK">
        <button type="button" className="btn btn-sm btn-outline mb-2" onClick={() => setPlaying(null)}>
          ← Danh sách
        </button>
        <div className="video-embed">
          <iframe
            src={embedSrc}
            title={playing.title}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowFullScreen
            referrerPolicy="strict-origin-when-cross-origin"
            loading="lazy"
          />
        </div>
        <p className="text-sm text-muted mt-2">
          Video không hiện?{' '}
          <a href={youtubeWatchUrl(playing.id)} target="_blank" rel="noopener noreferrer" style={{ color: 'var(--primary)' }}>
            Mở trên YouTube ↗
          </a>
        </p>
      </PageShell>
    );
  }

  return (
    <PageShell title="Video bài giảng HSK" desc="32 video bài giảng — phát nhúng hoặc trên YouTube">
      {fp && (
        <div className="card mb-3" style={{ padding: 0, overflow: 'hidden' }}>
          <div style={{ padding: '16px 20px', borderBottom: '1px solid var(--border)' }}>
            <h2 className="card-title">📺 {fp.title}</h2>
            <p className="card-desc">
              Phát cả playlist —{' '}
              <a href={fp.url} target="_blank" rel="noopener noreferrer" style={{ color: 'var(--primary)' }}>
                Mở trên YouTube
              </a>
            </p>
          </div>
          <div className="video-embed" style={{ borderRadius: 0 }}>
            <iframe
              src={plEmbed || fp.embedUrl}
              title="Playlist HSK"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowFullScreen
              referrerPolicy="strict-origin-when-cross-origin"
              loading="lazy"
            />
          </div>
        </div>
      )}

      {(vdata.playlists || []).map(pl => {
        if (pl.embedPlaylist && (!pl.videos || !pl.videos.length)) return null;
        if (!pl.videos?.length) return null;
        return (
          <div key={pl.id}>
            <h2 className="card-title mb-2 mt-3">
              {pl.name} {pl.premium ? <span className="pro-badge">PRO</span> : null}
            </h2>
            <div className="video-grid">
              {pl.videos.map(v => {
                const id = v.youtubeId || '';
                const invalid = !id || id.startsWith('PLACEHOLDER');
                const locked = (!v.free && !isPremium) || invalid;
                const thumb = !invalid
                  ? `https://i.ytimg.com/vi/${id}/mqdefault.jpg`
                  : 'linear-gradient(135deg,#1e40af,#0891b2)';
                const bgStyle = thumb.startsWith('http') ? `url('${thumb}') center/cover` : thumb;
                return (
                  <div
                    key={v.id}
                    className="card video-card card-hover"
                    role="button"
                    tabIndex={0}
                    onClick={() => playVideo(v)}
                    onKeyDown={e => { if (e.key === 'Enter') playVideo(v); }}
                  >
                    <div className="video-thumb" style={{ background: bgStyle }}>
                      {locked ? (
                        <div className="video-lock-overlay">
                          🔒<span className="text-sm">Premium</span>
                        </div>
                      ) : (
                        <div className="video-play"><span>▶</span></div>
                      )}
                    </div>
                    <div className="video-body">
                      <div className="card-title">{v.title}</div>
                      <div className="card-desc">
                        {v.duration || ''} · {v.level || ''}{v.free ? ' · Miễn phí' : ''}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        );
      })}

      {!vdata.playlists?.length && !fp && (
        <div className="empty-state"><p>Chưa có video</p></div>
      )}

      {!isPremium && (
        <p className="text-muted text-sm mt-3">
          <Link to="/premium">Nâng cấp Premium</Link> để mở khóa toàn bộ video.
        </p>
      )}
    </PageShell>
  );
}
