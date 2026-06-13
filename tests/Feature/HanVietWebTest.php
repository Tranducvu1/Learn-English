<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HanVietWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('app:publish-frontend');
    }

    public function test_home_renders_blade_spa(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('HANVIET_CONFIG', false)
            ->assertSee('hanviet-api', false)
            ->assertSee('id="page-dashboard"', false)
            ->assertSee('Đăng nhập', false);
    }

    public function test_spa_fallback_route(): void
    {
        $this->get('/lessons')
            ->assertOk()
            ->assertSee('id="page-lessons"', false);
    }

    public function test_static_assets_are_published(): void
    {
        $this->assertFileExists(public_path('css/style.css'));
        $this->assertFileExists(public_path('js/app.js'));
        $this->assertFileExists(public_path('js/api.js'));
        $this->assertFileDoesNotExist(public_path('index.html'));
    }
}
