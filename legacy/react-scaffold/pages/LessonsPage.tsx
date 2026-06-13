import { useCallback, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { PageShell } from '../components/ui/PageShell';
import { resolveWord } from '../lib/data';
import { markLessonComplete, markLessonOpened } from '../lib/storage';
import { voice } from '../lib/voice';
import type { Lesson, Level } from '../types/data';

const HSK_ICON: Record<string, string> = { hsk1: '1', hsk2: '2', hsk3: '3', hsk4: '4', hsk5: '5', hsk6: '6' };

function HanziWithPinyin({ hanzi, pinyin, showPinyin }: { hanzi: string; pinyin: string; showPinyin: boolean }) {
  if (!showPinyin) return <span>{hanzi}</span>;
  const chars = [...hanzi];
  const py = pinyin.split(/\s+/);
  return (
    <span className="ruby-text">
      {chars.map((c, i) => (
        <span key={i} style={{ display: 'inline-flex', flexDirection: 'column', alignItems: 'center', marginRight: 2 }}>
          {/[\u4e00-\u9fff]/.test(c) && <span className="pinyin text-sm">{py[i] || ''}</span>}
          <span>{c}</span>
        </span>
      ))}
    </span>
  );
}

function LessonCard({
  lesson, level, idx, done, started, topic, onOpen,
}: {
  lesson: Lesson;
  level: Level;
  idx: number;
  done: boolean;
  started: boolean;
  topic?: { icon: string; name: string };
  onOpen: (lesson: Lesson, level: Level) => void;
}) {
  return (
    <div
      className="card card-hover lesson-card"
      role="button"
      tabIndex={0}
      onClick={() => onOpen(lesson, level)}
      onKeyDown={e => { if (e.key === 'Enter') onOpen(lesson, level); }}
    >
      <div className="lesson-num" style={{ background: `${level.color}22`, color: level.color }}>{idx}</div>
      <div style={{ flex: 1 }}>
        <div className="flex-between">
          <span className="card-title">{lesson.title}</span>
          {done ? <span className="tag tag-done">✓ Hoàn thành</span> : started ? <span className="tag">Đang học</span> : null}
        </div>
        <div className="card-desc">
          {level.name}{topic ? ` · ${topic.icon} ${topic.name}` : ''} · ⏱ {lesson.duration} phút
        </div>
        <div className="lesson-tags">
          {(lesson.skills || []).map(sk => <span key={sk} className="tag">{sk}</span>)}
        </div>
      </div>
    </div>
  );
}

export function LessonsPage() {
  const { data, state, vocabMap, refreshState } = useApp();
  const [searchParams, setSearchParams] = useSearchParams();
  const activeHsk = searchParams.get('hsk') || null;
  const activeTopic = searchParams.get('topic') || null;
  const [selected, setSelected] = useState<{ lesson: Lesson; level: Level } | null>(null);

  const levels = data?.lessons.levels || [];
  const topics = data?.lessons.topics || [];

  const totalByLevel = useMemo(() => {
    const map: Record<string, number> = {};
    levels.forEach(l => { map[l.id] = l.lessons.length; });
    return map;
  }, [levels]);

  const setFilter = useCallback((key: 'hsk' | 'topic', value: string | null) => {
    const next = new URLSearchParams(searchParams);
    if (value) next.set(key, value);
    else next.delete(key);
    setSearchParams(next, { replace: true });
    setSelected(null);
  }, [searchParams, setSearchParams]);

  const openLesson = useCallback((lesson: Lesson, level: Level) => {
    markLessonOpened(lesson.id, level.id);
    refreshState();
    setSelected({ lesson, level });
  }, [refreshState]);

  const completeLesson = useCallback(() => {
    if (!selected) return;
    markLessonComplete(selected.lesson.id, selected.level.id, selected.lesson.title, totalByLevel);
    refreshState();
    setSelected(null);
    alert('🎉 Chúc mừng! Bạn đã hoàn thành bài học.');
  }, [selected, totalByLevel, refreshState]);

  const playWord = useCallback((idOrHanzi: string) => {
    const w = resolveWord(vocabMap, idOrHanzi);
    if (w) voice.speakWord(w);
    else voice.speak(idOrHanzi);
  }, [vocabMap]);

  if (!data) return null;

  let idx = 0;
  const lessonBlocks: React.ReactNode[] = [];
  levels.forEach(level => {
    if (activeHsk && level.id !== activeHsk) return;
    const lessons = level.lessons.filter(l => !activeTopic || l.topic === activeTopic);
    if (!lessons.length) return;
    lessonBlocks.push(
      <h3 key={level.id} className="lesson-level-head" style={{ color: level.color }}>{level.name}</h3>
    );
    lessons.forEach(lesson => {
      idx += 1;
      const done = state.completedLessons.includes(lesson.id);
      const started = !!state.lessonProgress[lesson.id]?.startedAt;
      const topic = topics.find(t => t.id === lesson.topic);
      lessonBlocks.push(
        <LessonCard
          key={lesson.id}
          lesson={lesson}
          level={level}
          idx={idx}
          done={done}
          started={started}
          topic={topic}
          onOpen={openLesson}
        />
      );
    });
  });

  if (selected) {
    const { lesson, level } = selected;
    const vocabIds = [...(lesson.vocabIds || [])];
    const showPinyin = state.settings.showPinyin;

    return (
      <PageShell title={lesson.title} desc={`${level.name} · ⏱ ${lesson.duration} phút`}>
        <button type="button" className="btn btn-sm btn-outline mb-2" onClick={() => setSelected(null)}>
          ← Quay lại
        </button>
        {lesson.content?.intro && <p className="mb-2">{lesson.content.intro}</p>}

        <h2 className="section-title mb-1">💬 Hội thoại</h2>
        <div className="mb-3">
          {(lesson.content?.dialogue || []).map((d, i) => (
            <div key={i} className="dialogue-item">
              <div className="dialogue-speaker">{d.speaker}</div>
              <div className="ruby-text">
                <HanziWithPinyin hanzi={d.hanzi} pinyin={d.pinyin} showPinyin={showPinyin} />
              </div>
              <div className="text-muted text-sm">{d.vietnamese}</div>
            </div>
          ))}
        </div>

        <h2 className="section-title mb-1">📚 Từ vựng</h2>
        <div className="card mb-3">
          {vocabIds.length ? vocabIds.map(key => {
            let w = resolveWord(vocabMap, key);
            if (!w && lesson.content?.dialogue) {
              const line = lesson.content.dialogue.find(d => d.hanzi?.includes(key));
              if (line) {
                w = {
                  id: key, hanzi: key, pinyin: line.pinyin, vietnamese: line.vietnamese, hsk: 1,
                  example: { hanzi: line.hanzi, vietnamese: line.vietnamese },
                };
              }
            }
            if (!w) {
              return <div key={key} className="text-sm text-muted">⚠ Chưa có trong từ điển: {key}</div>;
            }
            return (
              <div key={key} className="vocab-row">
                <span className="hanzi" style={{ fontSize: '1.5rem', minWidth: 56 }}>{w.hanzi}</span>
                <div>
                  <div className="pinyin">{w.pinyin || ''}</div>
                  <div>{w.vietnamese || ''}</div>
                  <div className="text-sm text-muted">
                    {w.example?.hanzi || ''} — {w.example?.vietnamese || ''}
                  </div>
                </div>
                <button type="button" className="btn btn-sm btn-outline" onClick={() => playWord(w!.id || w!.hanzi)}>
                  🔊
                </button>
              </div>
            );
          }) : <p className="text-muted">Xem hội thoại bên trên để học từ.</p>}
        </div>

        <div className="flex gap-1">
          <button type="button" className="btn btn-primary" onClick={completeLesson}>✓ Hoàn thành bài</button>
          <Link to="/luyen-de-hsk" className="btn btn-outline">📝 Làm quiz</Link>
        </div>
      </PageShell>
    );
  }

  return (
    <PageShell
      title="Luyện thi HSK & bài học tiếng Trung"
      desc="214 bài học HSK 1-6 theo chủ đề — hội thoại, từ vựng, luyện 4 kỹ năng"
    >
      <div className="topic-pills mb-2">
        <button
          type="button"
          className={`topic-pill hsk-pill${!activeHsk ? ' active' : ''}`}
          onClick={() => setFilter('hsk', null)}
        >
          Tất cả HSK
        </button>
        {levels.map(l => {
          const done = state.completedLessons.filter(id => id.startsWith(l.id)).length;
          const total = l.lessons.length;
          return (
            <button
              key={l.id}
              type="button"
              className={`topic-pill hsk-pill${activeHsk === l.id ? ' active' : ''}`}
              style={{ ['--pill-accent' as string]: l.color }}
              onClick={() => setFilter('hsk', l.id)}
            >
              HSK {HSK_ICON[l.id] || '?'} <span className="pill-count">{done}/{total}</span>
            </button>
          );
        })}
      </div>

      <div className="topic-pills mb-3">
        <button
          type="button"
          className={`topic-pill${!activeTopic ? ' active' : ''}`}
          onClick={() => setFilter('topic', null)}
        >
          Tất cả chủ đề
        </button>
        {topics.filter(t => (t.lessonCount || 0) > 0).map(t => (
          <button
            key={t.id}
            type="button"
            className={`topic-pill${activeTopic === t.id ? ' active' : ''}`}
            onClick={() => setFilter('topic', t.id)}
          >
            {t.icon} {t.name} ({t.lessonCount})
          </button>
        ))}
      </div>

      {lessonBlocks.length ? lessonBlocks : (
        <div className="empty-state">
          <div className="empty-icon">📭</div>
          <p>Chưa có bài cho bộ lọc này</p>
          <button type="button" className="btn btn-outline mt-2" onClick={() => { setSearchParams({}); setSelected(null); }}>
            Xóa bộ lọc
          </button>
        </div>
      )}
    </PageShell>
  );
}
