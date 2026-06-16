<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        return response(self::buildXml(self::urls()), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $sitemap = URL::to('/sitemap.xml');
        $deploy = trim((string) env('RENDER_GIT_COMMIT', ''));
        $body = "User-agent: *\nAllow: /\n\nSitemap: {$sitemap}\n";
        if ($deploy !== '') {
            $body .= "\n# deploy: {$deploy}\n";
        }

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /** @return list<array{loc: string, priority: string, changefreq: string}> */
    public static function urls(): array
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => url('/hoc-tieng-trung'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/luyen-thi-hsk'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/tu-vung-hsk'), 'priority' => '0.9', 'changefreq' => 'weekly'],
        ];

        foreach (range(1, 6) as $level) {
            $urls[] = [
                'loc' => url("/hsk-{$level}"),
                'priority' => '0.85',
                'changefreq' => 'weekly',
            ];
        }

        $urls[] = ['loc' => url('/privacy'), 'priority' => '0.3', 'changefreq' => 'yearly'];

        return $urls;
    }

  /** @param list<array{loc: string, priority: string, changefreq: string}> $urls */
    public static function buildXml(array $urls): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8').'</loc>';
            $lines[] = '    <changefreq>'.$url['changefreq'].'</changefreq>';
            $lines[] = '    <priority>'.$url['priority'].'</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
