<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HanVietApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\JsonDataSeeder::class);
    }

    public function test_health_endpoint(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJson(['ok' => true, 'service' => 'hanviet-api']);
    }

    public function test_bootstrap_returns_full_app_data_shape(): void
    {
        $response = $this->getJson('/api/v1/bootstrap');

        $response->assertOk()
            ->assertJsonStructure([
                'lessons' => ['levels', 'topics'],
                'vocabulary' => ['meta', 'words'],
                'quizzes' => ['quizzes'],
                'dictionary' => ['entries'],
                'videos' => ['playlists'],
                'premium' => ['pricing', 'features', 'roleplayScenarios'],
                'examTips' => ['general', 'levels', 'high_score'],
                'roadmap' => ['phases'],
                'premiumCompare',
            ]);

        $data = $response->json();
        $this->assertCount(6, $data['lessons']['levels']);
        $this->assertCount(1200, $data['vocabulary']['words']);
        $this->assertCount(86, $data['quizzes']['quizzes']);
        $this->assertNotEmpty($data['lessons']['levels'][0]['lessons'][0]['vocabIds']);
        $this->assertNotEmpty($data['lessons']['levels'][0]['lessons'][0]['content']['dialogue']);
        $this->assertArrayHasKey('correct', $data['quizzes']['quizzes'][0]['questions'][0]);
    }

    public function test_words_endpoint_supports_pagination_and_search(): void
    {
        $this->getJson('/api/v1/words?per_page=10')
            ->assertOk()
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonCount(10, 'words');

        $word = Word::first();
        $this->getJson('/api/v1/words/'.$word->id)
            ->assertOk()
            ->assertJsonPath('word.hanzi', $word->hanzi);
    }

    public function test_auth_register_login_and_progress(): void
    {
        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Learner',
            'email' => 'learner@hanviet.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $register->assertCreated()
            ->assertJsonStructure(['user', 'token']);

        $token = $register->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/me/progress')
            ->assertOk()
            ->assertJsonStructure([
                'streak', 'completedLessons', 'lessonProgress',
                'hskProgress', 'srsCards', 'settings',
            ]);

        $this->withToken($token)
            ->postJson('/api/v1/me/progress/sync', [
                'streak' => 3,
                'settings' => ['darkMode' => true, 'showPinyin' => true, 'fontSize' => 'medium'],
            ])
            ->assertOk()
            ->assertJsonPath('streak', 3)
            ->assertJsonPath('settings.darkMode', true);
    }

    public function test_premium_checkout_sandbox(): void
    {
        $user = User::factory()->create(['is_premium' => false]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/premium/checkout', [
                'plan' => 'monthly',
                'method' => 'sandbox',
            ])
            ->assertOk()
            ->assertJsonPath('isPremium', true)
            ->assertJsonStructure(['subscription' => ['plan', 'provider', 'ref']]);

        $user->refresh();
        $this->assertTrue($user->hasPremiumAccess());
    }

    public function test_premium_demo_and_ai_chat(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/premium/demo')
            ->assertOk()
            ->assertJsonPath('isPremium', true);

        $this->withToken($token)
            ->postJson('/api/v1/ai/tutor/chat', ['message' => '你好', 'hsk_level' => 'hsk1'])
            ->assertOk()
            ->assertJsonStructure(['session_id', 'reply', 'metadata'])
            ->assertJsonPath('metadata.rag', true);
    }

    public function test_ai_chat_requires_premium(): void
    {
        $user = User::factory()->create(['is_premium' => false]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/ai/tutor/chat', ['message' => '你好'])
            ->assertForbidden()
            ->assertJsonPath('code', 'premium_required');
    }

    public function test_unauthenticated_api_returns_json_401(): void
    {
        $this->getJson('/api/v1/me/progress')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_spa_index_is_served(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('hanviet-api', false);
    }
}
