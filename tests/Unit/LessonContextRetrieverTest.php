<?php

namespace Tests\Unit;

use App\Services\LessonContextRetriever;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonContextRetrieverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\JsonDataSeeder::class);
    }

    public function test_retrieves_context_for_hanzi_message(): void
    {
        $retriever = app(LessonContextRetriever::class);
        $result = $retriever->retrieve('你好', 'hsk1');

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('你好', implode(' ', $result));
    }

    public function test_retrieves_context_for_vietnamese_message(): void
    {
        $retriever = app(LessonContextRetriever::class);
        $result = $retriever->retrieve('cảm ơn', 'hsk1');

        $this->assertNotEmpty($result);
    }
}
