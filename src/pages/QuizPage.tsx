import { useCallback, useState } from 'react';
import { useApp } from '../context/AppContext';
import { PageShell } from '../components/ui/PageShell';
import { getState, saveState } from '../lib/storage';
import { voice } from '../lib/voice';
import type { Quiz } from '../types/data';

type QuizQuestion = Quiz['questions'][0] & { correct?: number };

type QuizState = {
  quiz: Quiz;
  index: number;
  score: number;
  answered: boolean;
};

function getCorrectIndex(q: QuizQuestion): number {
  if (typeof q.correct === 'number') return q.correct;
  return q.correctIndex ?? 0;
}

export function QuizPage() {
  const { data } = useApp();
  const [quizState, setQuizState] = useState<QuizState | null>(null);
  const [selectedChoice, setSelectedChoice] = useState<number | null>(null);

  const endQuiz = useCallback(() => {
    setQuizState(null);
    setSelectedChoice(null);
  }, []);

  const startQuiz = useCallback((quiz: Quiz) => {
    setQuizState({ quiz, index: 0, score: 0, answered: false });
    setSelectedChoice(null);
  }, []);

  const answerQuiz = useCallback((choice: number) => {
    if (!quizState || quizState.answered) return;
    const q = quizState.quiz.questions[quizState.index] as QuizQuestion;
    const correctIdx = getCorrectIndex(q);
    const correct = choice === correctIdx;
    setSelectedChoice(choice);
    setQuizState(prev => prev ? { ...prev, answered: true, score: correct ? prev.score + 1 : prev.score } : null);
  }, [quizState]);

  const nextQuiz = useCallback(() => {
    if (!quizState) return;
    const nextIndex = quizState.index + 1;
    if (nextIndex >= quizState.quiz.questions.length) {
      const scores = { ...getState().quizScores, [quizState.quiz.id]: quizState.score };
      saveState({ ...getState(), quizScores: scores });
      setQuizState(prev => prev ? { ...prev, index: nextIndex } : null);
      setSelectedChoice(null);
      return;
    }
    setQuizState(prev => prev ? { ...prev, index: nextIndex, answered: false } : null);
    setSelectedChoice(null);
  }, [quizState]);

  if (!data) return null;

  const quizzes = data.quizzes.quizzes;
  const totalQuestions = quizzes.reduce((s, q) => s + q.questions.length, 0);

  if (quizState) {
    const { quiz, index, score, answered } = quizState;
    const q = quiz.questions[index] as QuizQuestion | undefined;

    if (!q) {
      return (
        <PageShell title="Hoàn thành quiz" desc={quiz.title}>
          <div className="card" style={{ textAlign: 'center', padding: '2rem' }}>
            <h2>🎉 Hoàn thành!</h2>
            <p>Điểm: {score}/{quiz.questions.length}</p>
            <button type="button" className="btn btn-primary mt-2" onClick={endQuiz}>← Quay lại</button>
          </div>
        </PageShell>
      );
    }

    const correctIdx = getCorrectIndex(q);

    return (
      <PageShell title={quiz.title} desc={`Câu ${index + 1}/${quiz.questions.length}`}>
        <div className="card">
          <div className="text-sm text-muted mb-1">Câu {index + 1}/{quiz.questions.length}</div>
          <h2 className="mb-2">{q.question}</h2>
          {q.audioText && (
            <button type="button" className="btn btn-sm btn-outline mb-2" onClick={() => voice.speak(q.audioText!)}>
              🔊 Nghe
            </button>
          )}
          <div>
            {q.options.map((o, i) => {
              let cls = 'quiz-option';
              if (answered) {
                if (i === correctIdx) cls += ' correct';
                if (i === selectedChoice && i !== correctIdx) cls += ' wrong';
              }
              return (
                <button
                  key={i}
                  type="button"
                  className={cls}
                  disabled={answered}
                  onClick={() => answerQuiz(i)}
                >
                  {o}
                </button>
              );
            })}
          </div>
          {answered && (
            <div className="mt-2">
              <p style={{ color: selectedChoice === correctIdx ? 'var(--success)' : '#ef4444' }}>
                {selectedChoice === correctIdx ? '✓ Chính xác!' : '✗ Sai rồi'}
              </p>
              {q.explanation && <p className="text-sm text-muted">{q.explanation}</p>}
              <button type="button" className="btn btn-primary mt-2" onClick={nextQuiz}>Tiếp →</button>
            </div>
          )}
        </div>
      </PageShell>
    );
  }

  return (
    <PageShell
      title="Luyện đề thi thử HSK"
      desc={`${quizzes.length} đề thi · ${totalQuestions} câu hỏi từ 1200+ từ vựng`}
    >
      <div className="grid-2">
        {quizzes.length ? quizzes.map(q => (
          <div
            key={q.id}
            className="card card-hover"
            role="button"
            tabIndex={0}
            onClick={() => startQuiz(q)}
            onKeyDown={e => { if (e.key === 'Enter') startQuiz(q); }}
          >
            <div className="card-title">📝 {q.title}</div>
            <div className="card-desc">{q.questions.length} câu · Giải thích chi tiết</div>
            <span className="btn btn-primary btn-sm mt-2">Làm bài →</span>
          </div>
        )) : (
          <div className="empty-state"><p>Chưa có đề thi</p></div>
        )}
      </div>
    </PageShell>
  );
}
