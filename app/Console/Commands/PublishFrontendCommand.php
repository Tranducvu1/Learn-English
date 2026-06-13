<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishFrontendCommand extends Command
{
    protected $signature = 'app:publish-frontend {--force : Ghi đè file public}';

    protected $description = 'Publish SPA assets từ resources/ → public/ (css, js)';

    public function handle(): int
    {
        $published = 0;

        $cssSrc = resource_path('css/style.css');
        $cssDest = public_path('css/style.css');
        if (File::exists($cssSrc)) {
            File::ensureDirectoryExists(dirname($cssDest));
            File::copy($cssSrc, $cssDest);
            $this->line('✓ css/style.css');
            $published++;
        }

        $jsSrc = resource_path('js');
        $jsDest = public_path('js');
        if (File::isDirectory($jsSrc)) {
            File::ensureDirectoryExists($jsDest);
            foreach (File::files($jsSrc) as $file) {
                if ($file->getExtension() !== 'js') {
                    continue;
                }
                if ($file->getFilename() === 'data-bundle.js') {
                    continue;
                }
                File::copy($file->getPathname(), $jsDest.'/'.$file->getFilename());
                $this->line('✓ js/'.$file->getFilename());
                $published++;
            }
        }

        if (File::exists(public_path('index.html'))) {
            File::delete(public_path('index.html'));
            $this->line('✓ removed legacy public/index.html');
        }

        if (File::exists(public_path('js/data-bundle.js'))) {
            File::delete(public_path('js/data-bundle.js'));
            $this->line('✓ removed legacy js/data-bundle.js');
        }

        if ($published === 0) {
            $this->warn('Không có asset nào được publish.');

            return self::FAILURE;
        }

        $this->info('Frontend published → '.public_path());

        return self::SUCCESS;
    }
}
