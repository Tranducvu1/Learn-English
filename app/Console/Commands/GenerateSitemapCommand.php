<?php

namespace App\Console\Commands;

use App\Http\Controllers\Web\SeoController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'app:generate-sitemap';

    protected $description = 'Write public/sitemap.xml for static serving (Search Console, crawlers)';

    public function handle(): int
    {
        $xml = SeoController::buildXml(SeoController::urls());
        $path = public_path('sitemap.xml');
        File::put($path, $xml);

        $this->info('Sitemap written → '.$path);

        return self::SUCCESS;
    }
}
