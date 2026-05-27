import { Link } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { getDueCards } from '../lib/srs';
import { PageShell } from '../components/ui/PageShell';

const HSK_ICON: Record<string, string> = { hsk1: '1', hsk2: '2', hsk3: '3', hsk4: '4', hsk5: '5', hsk6: '6' };

export function HomePage() {
  const { data, state } = useApp();
  if (!data) return null;

  const vocab = data.vocabulary.words;
  const due = getDueCards(vocab, state.srsCards);
  const levels = data.lessons.levels;
  const topics = data.lessons.topics.filter(t => (t.lessonCount || 0) > 0);
  const lessonTotal = levels.reduce((n, l) => n + l.lessons.length, 0);

  return (
    <>
      <section className="hero">
        <div className="container hero-grid">
          <div className="hero-content">
            <h1>Học tiếng Trung &amp; luyện thi HSK online miễn phí</h1>
            <p>
              Chào mừng đến <strong>汉越学堂</strong> — nền tảng học tiếng Trung (汉语) với bài học HSK 1-6,
              1200+ từ vựng, flashcard SRS, luyện đề thi thử và video bài giảng.
            </p>
            <div className="flex gap-2 flex-wrap">
              <Link to="/luyen-hsk" className="btn btn-white">Bắt đầu học HSK 1</Link>
              <Link to="/tu-vung-hsk" className="btn btn-outline" style={{ color: '#fff', borderColor: 'rgba(255,255,255,.4)' }}>
                Xem từ vựng
              </Link>
            </div>
            <div className="hero-stats">
              <div className="hero-stat"><strong>{state.wordsLearned || 0}</strong><span>từ đã học</span></div>
              <div className="hero-stat"><strong>{state.completedLessons.length}</strong><span>bài hoàn thành</span></div>
              <div className="hero-stat"><strong>{vocab.length}+</strong><span>từ vựng HSK</span></div>
            </div>
          </div>
        </div>
      </section>

      <PageShell title="Bảng điều khiển" desc={`${lessonTotal} bài · ${data.quizzes.quizzes.length} quiz · ${vocab.length} từ`}>
        <div className="stats-row mb-3">
          <Stat icon="🔥" num={state.streak} label="Streak ngày" />
          <Stat icon="📚" num={state.wordsLearned} label="Từ đã học" />
          <Stat icon="⏱️" num={state.totalStudyMinutes} label="Phút học" />
          <Stat icon="🃏" num={due.length} label="Thẻ cần ôn" />
        </div>

        <h2 className="card-title mb-2">Chọn cấp độ HSK</h2>
        <div className="exam-grid mb-3">
          {levels.map(l => {
            const done = state.completedLessons.filter(id => id.startsWith(l.id)).length;
            const total = l.lessons.length || 1;
            const pct = Math.min(100, Math.round((done / total) * 100));
            return (
              <Link key={l.id} to={`/luyen-hsk?hsk=${l.id}`} className="exam-card" style={{ ['--card-color' as string]: l.color, textDecoration: 'none', color: 'inherit' }}>
                <div className="exam-icon" style={{ background: l.color }}>HSK {HSK_ICON[l.id]}</div>
                <h3>{l.name}</h3>
                <p>{l.description}</p>
                <div className="exam-meta"><span>{total} bài</span><span>{pct}%</span></div>
                <div className="progress-track"><div className="progress-fill" style={{ width: `${pct}%`, background: l.color }} /></div>
              </Link>
            );
          })}
        </div>

        <h2 className="card-title mb-2">Chủ đề</h2>
        <div className="topic-pills">
          {topics.map(t => (
            <Link key={t.id} to={`/luyen-hsk?topic=${t.id}`} className="topic-pill">
              {t.icon} {t.name} ({t.lessonCount})
            </Link>
          ))}
        </div>

        <nav className="grid-3 mt-3" aria-label="Liên kết nội bộ">
          <Link to="/tu-vung-hsk" className="card card-hover"><h3 className="card-title">📚 Từ vựng</h3><p className="card-desc">1200 từ HSK</p></Link>
          <Link to="/luyen-de-hsk" className="card card-hover"><h3 className="card-title">📝 Luyện đề</h3><p className="card-desc">86 đề quiz</p></Link>
          <Link to="/video-bai-giang-hsk" className="card card-hover"><h3 className="card-title">🎬 Video</h3><p className="card-desc">32 bài giảng</p></Link>
        </nav>
      </PageShell>
    </>
  );
}

function Stat({ icon, num, label }: { icon: string; num: number; label: string }) {
  return (
    <div className="stat-box">
      <div className="stat-icon">{icon}</div>
      <div>
        <div className="stat-num">{num}</div>
        <div className="stat-label">{label}</div>
      </div>
    </div>
  );
}
