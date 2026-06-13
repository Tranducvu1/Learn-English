import { useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { PageShell } from '../components/ui/PageShell';
import { voice } from '../lib/voice';
import type { Word } from '../types/data';

const PAGE_SIZE = 50;

export function VocabularyPage() {
  const { data } = useApp();
  const [searchParams, setSearchParams] = useSearchParams();
  const [search, setSearch] = useState('');
  const hskFilter = searchParams.get('hsk') || '';
  const activeTopic = searchParams.get('topic') || '';
  const page = Math.max(0, parseInt(searchParams.get('page') || '0', 10) || 0);

  const words = data?.vocabulary.words || [];
  const topics = data?.lessons.topics || [];
  const total = words.length;

  const topicCounts = useMemo(() => {
    const counts: Record<string, number> = {};
    words.forEach(w => {
      if (hskFilter && String(w.hsk) !== hskFilter) return;
      if (w.topic) counts[w.topic] = (counts[w.topic] || 0) + 1;
    });
    return counts;
  }, [words, hskFilter]);

  const filtered = useMemo(() => {
    const q = search.toLowerCase().trim();
    return words.filter(w => {
      if (hskFilter && String(w.hsk) !== hskFilter) return false;
      if (activeTopic && w.topic !== activeTopic) return false;
      if (!q) return true;
      return (
        w.hanzi.includes(q) ||
        w.pinyin.toLowerCase().includes(q) ||
        (w.vietnamese || '').toLowerCase().includes(q) ||
        (w.english || '').toLowerCase().includes(q)
      );
    });
  }, [words, hskFilter, activeTopic, search]);

  const pages = Math.ceil(filtered.length / PAGE_SIZE) || 1;
  const slice = filtered.slice(page * PAGE_SIZE, page * PAGE_SIZE + PAGE_SIZE);
  const topicName = topics.find(t => t.id === activeTopic)?.name;

  const setParam = (key: string, value: string | null) => {
    const next = new URLSearchParams(searchParams);
    if (value) next.set(key, value);
    else next.delete(key);
    if (key !== 'page') next.delete('page');
    setSearchParams(next, { replace: true });
  };

  const playWord = (w: Word) => voice.speakWord(w);

  if (!data) return null;

  return (
    <PageShell
      title="Kho từ vựng HSK 1-6"
      desc={`${total} từ vựng · Hiển thị ${filtered.length}${topicName ? ` · ${topicName}` : ''}`}
    >
      <div className="flex gap-2 mb-2 flex-wrap">
        <input
          type="search"
          className="card"
          style={{ flex: 1, minWidth: 200, padding: '10px 16px' }}
          placeholder="Tìm Hán tự, pinyin, nghĩa..."
          value={search}
          onChange={e => { setSearch(e.target.value); setParam('page', '0'); }}
        />
        <select
          className="card"
          style={{ padding: '10px 16px' }}
          value={hskFilter}
          onChange={e => { setParam('hsk', e.target.value || null); setParam('topic', null); }}
        >
          <option value="">Tất cả HSK</option>
          {[1, 2, 3, 4, 5, 6].map(n => (
            <option key={n} value={String(n)}>HSK {n}</option>
          ))}
        </select>
      </div>

      <div className="topic-pills mb-3">
        <button
          type="button"
          className={`topic-pill${!activeTopic ? ' active' : ''}`}
          onClick={() => setParam('topic', null)}
        >
          Tất cả chủ đề
        </button>
        {topics.filter(t => topicCounts[t.id]).map(t => (
          <button
            key={t.id}
            type="button"
            className={`topic-pill${activeTopic === t.id ? ' active' : ''}`}
            onClick={() => setParam('topic', t.id)}
          >
            {t.icon} {t.name} ({topicCounts[t.id]})
          </button>
        ))}
      </div>

      <div id="vocabList">
        {slice.length ? slice.map(w => {
          const tMeta = topics.find(t => t.id === w.topic);
          return (
            <div key={w.id} className="dict-item" role="button" tabIndex={0} onClick={() => playWord(w)}>
              <span className="dict-hanzi">{w.hanzi}</span>
              <div style={{ flex: 1 }}>
                <div className="flex-between">
                  <span className="pinyin">{w.pinyin}</span>
                  <span className="tag">HSK {w.hsk}{tMeta ? ` · ${tMeta.icon}` : ''}</span>
                </div>
                <div style={{ fontWeight: 500 }}>{w.vietnamese}</div>
                {w.english && w.english !== w.vietnamese && (
                  <div className="text-sm text-muted">{w.english}</div>
                )}
              </div>
              <button type="button" className="btn btn-sm btn-outline" onClick={e => { e.stopPropagation(); playWord(w); }}>
                🔊
              </button>
            </div>
          );
        }) : (
          <div className="empty-state"><p>Không có từ</p></div>
        )}
      </div>

      <div className="flex-between mt-3">
        <button type="button" className="btn btn-outline btn-sm" disabled={page === 0} onClick={() => setParam('page', String(page - 1))}>
          ← Trước
        </button>
        <span className="text-muted">Trang {page + 1}/{pages}</span>
        <button type="button" className="btn btn-outline btn-sm" disabled={page + 1 >= pages} onClick={() => setParam('page', String(page + 1))}>
          Sau →
        </button>
      </div>
    </PageShell>
  );
}
