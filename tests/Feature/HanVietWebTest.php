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
        $this->assertFileExists(public_path('og/share.svg'));
    }

    public function test_sitemap_xml(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('/hsk-1', false)
            ->assertSee('/luyen-thi-hsk', false);
    }

    public function test_robots_txt_points_to_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap:', false)
            ->assertSee('sitemap.xml', false);
    }

    public function test_home_has_seo_meta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('og:title', false)
            ->assertSee('canonical', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_landing_hsk_1(): void
    {
        $this->get('/hsk-1')
            ->assertOk()
            ->assertSee('Học tiếng Trung HSK 1', false)
            ->assertSee('page=lessons', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_landing_hoc_tieng_trung(): void
    {
        $this->get('/hoc-tieng-trung')
            ->assertOk()
            ->assertSee('Học tiếng Trung online', false);
    }
}
