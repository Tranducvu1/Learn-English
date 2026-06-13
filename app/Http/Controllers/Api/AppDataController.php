<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DictionaryEntry;
use App\Models\FeaturedPlaylist;
use App\Models\Lesson;
use App\Models\LessonDialogue;
use App\Models\Level;
use App\Models\PremiumFeature;
use App\Models\PremiumPlan;
use App\Models\Quiz;
use App\Models\RoleplayScenario;
use App\Models\Topic;
use App\Models\VideoPlaylist;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Trả về đúng shape window.APP_DATA — frontend không cần refactor lớn.
 */
class AppDataController extends Controller
{
    public function bootstrap(): JsonResponse
    {
        $data = Cache::get('hanviet_bootstrap_v2');

        if (! is_array($data) || ! is_array($data['vocabulary']['words'] ?? null) || count($data['vocabulary']['words']) < 100) {
            Cache::forget('hanviet_bootstrap_v2');
            $data = $this->build();
            Cache::put('hanviet_bootstrap_v2', $data, now()->addHour());
        }

        return response()->json($data);
    }

    private function build(): array
    {
        $levels = Level::with([
            'lessons' => fn ($q) => $q->with(['dialogues', 'words'])->orderBy('id'),
        ])->orderBy('sort_order')->get();

        $topics = Topic::orderBy('name')->get();

        $words = Word::orderBy('id')->get();
        $sentenceMap = $this->buildSentenceMap($words);

        $quizzes = Quiz::with('questions')->orderBy('id')->get();

        $dictionary = DictionaryEntry::orderBy('hanzi')->get();

        $playlists = VideoPlaylist::with('videos')->get();
        $featured = FeaturedPlaylist::first();

        $plans = PremiumPlan::all()->keyBy('slug');

        return [
            'lessons' => [
                'levels' => $levels->map(fn (Level $level) => [
                    'id' => $level->id,
                    'name' => $level->name,
                    'color' => $level->color,
                    'description' => $level->description,
                    'totalLessons' => $level->lessons->count(),
                    'lessons' => $level->lessons->map(fn (Lesson $l) => $this->formatLesson($l)),
                ]),
                'topics' => $topics->map(fn (Topic $t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'icon' => $t->icon,
                    'lessonCount' => $t->lesson_count,
                ]),
            ],
            'vocabulary' => [
                'meta' => ['count' => $words->count(), 'perLevel' => 200],
                'words' => $words->map(fn (Word $w) => $this->formatWord($w, $sentenceMap[$w->id] ?? [])),
            ],
            'quizzes' => [
                'quizzes' => $quizzes->map(fn (Quiz $q) => [
                    'id' => $q->id,
                    'title' => $q->title,
                    'level' => $q->level_id,
                    'lessonId' => $q->lesson_id,
                    'questions' => $q->questions->map(fn ($question) => [
                        'id' => $question->external_id ?? (string) $question->id,
                        'type' => $question->type,
                        'question' => $question->question,
                        'hanzi' => $question->hanzi,
                        'audioText' => $question->audio_text,
                        'options' => $question->options,
                        'correct' => $question->correct_index,
                        'explanation' => $question->explanation,
                    ]),
                ]),
            ],
            'dictionary' => [
                'entries' => $dictionary->map(fn ($e) => [
                    'hanzi' => $e->hanzi,
                    'pinyin' => $e->pinyin,
                    'vietnamese' => $e->vietnamese,
                    'hsk' => $e->hsk,
                    'pos' => $e->pos,
                    'examples' => $e->examples,
                ]),
            ],
            'videos' => [
                'featuredPlaylist' => $featured ? [
                    'id' => $featured->playlist_id,
                    'title' => $featured->title,
                    'embedUrl' => $featured->embed_url,
                    'url' => $featured->url,
                ] : null,
                'playlists' => $playlists->map(fn (VideoPlaylist $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'source' => $p->source,
                    'playlistId' => $p->playlist_id,
                    'playlistUrl' => $p->playlist_url,
                    'description' => $p->description,
                    'premium' => $p->premium,
                    'embedPlaylist' => $p->embed_playlist,
                    'videos' => $p->videos->map(fn ($v) => [
                        'id' => $v->id,
                        'youtubeId' => $v->youtube_id,
                        'title' => $v->title,
                        'duration' => $v->duration,
                        'level' => $v->level_id,
                        'topic' => $v->topic_id,
                        'free' => $v->free,
                        'hasSubtitle' => $v->has_subtitle,
                        'tags' => $v->tags,
                    ]),
                ]),
            ],
            'premium' => [
                'pricing' => [
                    'monthly' => isset($plans['monthly']) ? [
                        'amount' => $plans['monthly']->amount,
                        'currency' => $plans['monthly']->currency,
                        'label' => $plans['monthly']->label,
                    ] : null,
                    'yearly' => isset($plans['yearly']) ? [
                        'amount' => $plans['yearly']->amount,
                        'currency' => $plans['yearly']->currency,
                        'label' => $plans['yearly']->label,
                        'savings' => $plans['yearly']->savings,
                    ] : null,
                ],
                'features' => PremiumFeature::orderBy('sort_order')->get()->map(fn ($f) => [
                    'id' => $f->id,
                    'icon' => $f->icon,
                    'title' => $f->title,
                    'tagline' => $f->tagline,
                    'description' => $f->description,
                    'highlights' => $f->highlights,
                ]),
                'roleplayScenarios' => RoleplayScenario::all()->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'level' => $s->level_id,
                ]),
                'paymentMode' => config('hanviet.premium.payment_mode', 'sandbox'),
            ],
            'examMatrix' => config('hsk_exam'),
            'examTips' => config('exam_tips'),
            'roadmap' => config('learning_roadmap'),
            'premiumCompare' => [
                'free' => ['Quảng cáo nhẹ', 'Đề mini free', 'Video cơ bản', 'Flashcard SRS'],
                'pro' => ['Không quảng cáo', 'AI Tutor + RAG', 'Video VIP', 'Lộ trình AI', 'Phát âm Pro', 'Đề mock đầy đủ'],
            ],
        ];
    }

    private function formatLesson(Lesson $lesson): array
    {
        return [
            'id' => $lesson->id,
            'title' => $lesson->title,
            'topic' => $lesson->topic_id,
            'duration' => $lesson->duration,
            'vocabIds' => $lesson->words->pluck('hanzi')->values()->all(),
            'skills' => $lesson->skills ?? [],
            'content' => [
                'intro' => $lesson->intro,
                'dialogue' => $lesson->dialogues->map(fn ($d) => [
                    'speaker' => $d->speaker,
                    'hanzi' => $d->hanzi,
                    'pinyin' => $d->pinyin,
                    'vietnamese' => $d->vietnamese,
                ]),
            ],
        ];
    }

    private function formatWord(Word $word, array $sentences = []): array
    {
        return [
            'id' => $word->id,
            'hanzi' => $word->hanzi,
            'pinyin' => $word->pinyin,
            'vietnamese' => $word->vietnamese,
            'english' => $word->english,
            'hsk' => $word->hsk,
            'topic' => $word->topic_id,
            'example' => $word->example,
            'sentences' => $sentences,
            'sentenceCount' => count($sentences),
        ];
    }

    /**
     * Gộp câu từ hội thoại bài học + câu hỏi quiz theo từ Hán.
     *
     * @return array<string, list<array{hanzi: string, pinyin: ?string, vietnamese: ?string, source: string}>>
     */
    private function buildSentenceMap($words): array
    {
        $map = [];
        foreach ($words as $word) {
            $map[$word->id] = [];
        }

        $dialogues = LessonDialogue::query()
            ->with('lesson:id,title')
            ->orderBy('lesson_id')
            ->orderBy('sort_order')
            ->get();

        foreach ($words as $word) {
            $hanzi = $word->hanzi;
            foreach ($dialogues as $dialogue) {
                if (! str_contains($dialogue->hanzi, $hanzi)) {
                    continue;
                }
                if (count($map[$word->id]) >= 6) {
                    break;
                }
                $map[$word->id][] = [
                    'hanzi' => $dialogue->hanzi,
                    'pinyin' => $dialogue->pinyin,
                    'vietnamese' => $dialogue->vietnamese,
                    'source' => $dialogue->lesson?->title ?? 'Hội thoại',
                ];
            }
        }

        foreach (Quiz::with('questions')->get() as $quiz) {
            foreach ($quiz->questions as $question) {
                $text = $question->hanzi ?: $question->question;
                if (! $text) {
                    continue;
                }
                foreach ($words as $word) {
                    if (! str_contains($text, $word->hanzi)) {
                        continue;
                    }
                    if (count($map[$word->id]) >= 8) {
                        continue;
                    }
                    $exists = collect($map[$word->id])->contains(fn ($s) => $s['hanzi'] === $text);
                    if ($exists) {
                        continue;
                    }
                    $map[$word->id][] = [
                        'hanzi' => $text,
                        'pinyin' => null,
                        'vietnamese' => $question->explanation,
                        'source' => $quiz->title,
                    ];
                }
            }
        }

        return $map;
    }
}
