<?php

namespace Database\Seeders;

use App\Models\DictionaryEntry;
use App\Models\FeaturedPlaylist;
use App\Models\Lesson;
use App\Models\LessonDialogue;
use App\Models\Level;
use App\Models\PremiumFeature;
use App\Models\PremiumPlan;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\RoleplayScenario;
use App\Models\Topic;
use App\Models\Video;
use App\Models\VideoPlaylist;
use App\Models\Word;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JsonDataSeeder extends Seeder
{
    private string $dataPath;

    private array $hanziToWordId = [];

    public function run(): void
    {
        $this->dataPath = config('hanviet.data_path', database_path('data'));

        DB::transaction(function () {
            $this->seedLevelsAndTopics();
            $this->seedWords();
            $this->seedLessons();
            $this->seedDictionary();
            $this->seedQuizzes();
            $this->seedVideos();
            $this->seedPremium();
        });

        $this->command->info('Imported: '.Level::count().' levels, '.Topic::count().' topics, '
            .Word::count().' words, '.Lesson::count().' lessons, '
            .Quiz::count().' quizzes, '.Video::count().' videos');

        Cache::forget('hanviet_bootstrap_v1');
    }

    private function readJson(string $file): array
    {
        $path = $this->dataPath.'/'.$file;
        if (! file_exists($path)) {
            throw new \RuntimeException("Missing data file: {$path}");
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function seedLevelsAndTopics(): void
    {
        $lessons = $this->readJson('lessons.json');

        foreach ($lessons['levels'] as $i => $level) {
            Level::updateOrCreate(
                ['id' => $level['id']],
                [
                    'name' => $level['name'],
                    'color' => $level['color'],
                    'description' => $level['description'] ?? null,
                    'sort_order' => $i + 1,
                ]
            );
        }

        foreach ($lessons['topics'] as $topic) {
            Topic::updateOrCreate(
                ['id' => $topic['id']],
                [
                    'name' => $topic['name'],
                    'icon' => $topic['icon'] ?? null,
                    'lesson_count' => $topic['lessonCount'] ?? 0,
                ]
            );
        }
    }

    private function seedWords(): void
    {
        $vocab = $this->readJson('vocabulary.json');

        foreach ($vocab['words'] as $word) {
            $topicId = $word['topic'] ?? null;
            if ($topicId && ! Topic::where('id', $topicId)->exists()) {
                $topicId = null;
            }

            Word::updateOrCreate(
                ['id' => $word['id']],
                [
                    'hanzi' => $word['hanzi'],
                    'pinyin' => $word['pinyin'] ?? null,
                    'vietnamese' => $word['vietnamese'] ?? null,
                    'english' => $word['english'] ?? null,
                    'hsk' => $word['hsk'],
                    'topic_id' => $topicId,
                    'example' => $word['example'] ?? null,
                ]
            );

            $this->hanziToWordId[$word['hanzi']] = $word['id'];
        }
    }

    private function seedLessons(): void
    {
        $lessons = $this->readJson('lessons.json');
        $pivotRows = [];

        foreach ($lessons['levels'] as $level) {
            foreach ($level['lessons'] as $lesson) {
                $topicId = $lesson['topic'] ?? null;
                if ($topicId && ! Topic::where('id', $topicId)->exists()) {
                    $topicId = null;
                }

                Lesson::updateOrCreate(
                    ['id' => $lesson['id']],
                    [
                        'level_id' => $level['id'],
                        'topic_id' => $topicId,
                        'title' => $lesson['title'],
                        'duration' => $lesson['duration'] ?? 15,
                        'intro' => $lesson['content']['intro'] ?? null,
                        'skills' => $lesson['skills'] ?? [],
                    ]
                );

                LessonDialogue::where('lesson_id', $lesson['id'])->delete();

                foreach ($lesson['content']['dialogue'] ?? [] as $i => $line) {
                    LessonDialogue::create([
                        'lesson_id' => $lesson['id'],
                        'speaker' => $line['speaker'] ?? null,
                        'hanzi' => $line['hanzi'],
                        'pinyin' => $line['pinyin'] ?? null,
                        'vietnamese' => $line['vietnamese'] ?? null,
                        'sort_order' => $i,
                    ]);
                }

                foreach ($lesson['vocabIds'] ?? [] as $i => $ref) {
                    $wordId = $this->resolveWordRef($ref);
                    if ($wordId) {
                        $pivotRows[] = [
                            'lesson_id' => $lesson['id'],
                            'word_id' => $wordId,
                            'sort_order' => $i,
                        ];
                    }
                }
            }
        }

        DB::table('lesson_word')->delete();
        foreach (array_chunk($pivotRows, 200) as $chunk) {
            DB::table('lesson_word')->insert($chunk);
        }
    }

    private function resolveWordRef(string $ref): ?string
    {
        if (isset($this->hanziToWordId[$ref])) {
            return $this->hanziToWordId[$ref];
        }

        $word = Word::where('id', $ref)->orWhere('hanzi', $ref)->first();

        return $word?->id;
    }

    private function seedDictionary(): void
    {
        $dict = $this->readJson('dictionary.json');
        DictionaryEntry::query()->delete();

        foreach ($dict['entries'] as $entry) {
            DictionaryEntry::create([
                'hanzi' => $entry['hanzi'],
                'pinyin' => $entry['pinyin'] ?? null,
                'vietnamese' => $entry['vietnamese'] ?? null,
                'hsk' => $entry['hsk'] ?? null,
                'pos' => $entry['pos'] ?? null,
                'examples' => $entry['examples'] ?? null,
            ]);
        }
    }

    private function seedQuizzes(): void
    {
        $data = $this->readJson('quizzes.json');

        foreach ($data['quizzes'] as $quiz) {
            $levelId = $quiz['level'] ?? null;
            if ($levelId && ! Level::where('id', $levelId)->exists()) {
                $levelId = null;
            }

            $lessonId = $quiz['lessonId'] ?? null;
            if ($lessonId && ! Lesson::where('id', $lessonId)->exists()) {
                $lessonId = null;
            }

            Quiz::updateOrCreate(
                ['id' => $quiz['id']],
                [
                    'title' => $quiz['title'],
                    'level_id' => $levelId,
                    'lesson_id' => $lessonId,
                ]
            );

            QuizQuestion::where('quiz_id', $quiz['id'])->delete();

            foreach ($quiz['questions'] as $i => $q) {
                QuizQuestion::create([
                    'quiz_id' => $quiz['id'],
                    'external_id' => $q['id'] ?? null,
                    'type' => $q['type'],
                    'question' => $q['question'],
                    'hanzi' => $q['hanzi'] ?? null,
                    'audio_text' => $q['audioText'] ?? null,
                    'options' => $q['options'] ?? [],
                    'correct_index' => $q['correctIndex'] ?? $q['correct'] ?? 0,
                    'explanation' => $q['explanation'] ?? null,
                    'sort_order' => $i,
                ]);
            }
        }
    }

    private function seedVideos(): void
    {
        $data = $this->readJson('videos.json');

        FeaturedPlaylist::query()->delete();
        if (! empty($data['featuredPlaylist'])) {
            $fp = $data['featuredPlaylist'];
            FeaturedPlaylist::create([
                'playlist_id' => $fp['id'],
                'title' => $fp['title'],
                'embed_url' => $fp['embedUrl'],
                'url' => $fp['url'],
            ]);
        }

        foreach ($data['playlists'] as $playlist) {
            VideoPlaylist::updateOrCreate(
                ['id' => $playlist['id']],
                [
                    'name' => $playlist['name'],
                    'source' => $playlist['source'] ?? 'youtube',
                    'playlist_id' => $playlist['playlistId'] ?? null,
                    'playlist_url' => $playlist['playlistUrl'] ?? null,
                    'description' => $playlist['description'] ?? null,
                    'premium' => $playlist['premium'] ?? false,
                    'embed_playlist' => $playlist['embedPlaylist'] ?? false,
                ]
            );

            foreach ($playlist['videos'] as $video) {
                $levelId = $video['level'] ?? null;
                $topicId = $video['topic'] ?? null;

                Video::updateOrCreate(
                    ['id' => $video['id']],
                    [
                        'playlist_id' => $playlist['id'],
                        'youtube_id' => $video['youtubeId'],
                        'title' => $video['title'],
                        'duration' => $video['duration'] ?? null,
                        'level_id' => $levelId && Level::where('id', $levelId)->exists() ? $levelId : null,
                        'topic_id' => $topicId && Topic::where('id', $topicId)->exists() ? $topicId : null,
                        'free' => $video['free'] ?? true,
                        'has_subtitle' => $video['hasSubtitle'] ?? false,
                        'tags' => $video['tags'] ?? null,
                    ]
                );
            }
        }
    }

    private function seedPremium(): void
    {
        $data = $this->readJson('premium.json');

        foreach (['monthly', 'yearly'] as $slug) {
            if (! empty($data['pricing'][$slug])) {
                $p = $data['pricing'][$slug];
                PremiumPlan::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'amount' => $p['amount'],
                        'currency' => $p['currency'] ?? 'VND',
                        'label' => $p['label'],
                        'savings' => $p['savings'] ?? null,
                    ]
                );
            }
        }

        foreach ($data['features'] as $i => $feature) {
            PremiumFeature::updateOrCreate(
                ['id' => $feature['id']],
                [
                    'icon' => $feature['icon'] ?? null,
                    'title' => $feature['title'],
                    'tagline' => $feature['tagline'] ?? null,
                    'description' => $feature['description'] ?? null,
                    'highlights' => $feature['highlights'] ?? [],
                    'sort_order' => $i,
                ]
            );
        }

        foreach ($data['roleplayScenarios'] as $scenario) {
            $levelId = $scenario['level'] ?? null;
            RoleplayScenario::updateOrCreate(
                ['id' => $scenario['id']],
                [
                    'title' => $scenario['title'],
                    'level_id' => $levelId && Level::where('id', $levelId)->exists() ? $levelId : null,
                ]
            );
        }
    }
}
