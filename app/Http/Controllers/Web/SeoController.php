<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class SeoController extends Controller
{
    public function sitemap(): Response
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

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $sitemap = URL::to('/sitemap.xml');
        $body = "User-agent: *\nAllow: /\n\nSitemap: {$sitemap}\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
