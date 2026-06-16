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

        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $writer->startElement('url');
            $writer->writeElement('loc', $url['loc']);
            $writer->writeElement('changefreq', $url['changefreq']);
            $writer->writeElement('priority', $url['priority']);
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();

        return response($writer->outputMemory(), 200, [
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
