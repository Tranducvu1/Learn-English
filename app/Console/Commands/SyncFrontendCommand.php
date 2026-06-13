<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/** @deprecated Use app:publish-frontend */
class SyncFrontendCommand extends Command
{
    protected $signature = 'app:sync-frontend';

    protected $description = '[deprecated] Alias → app:publish-frontend';

    public function handle(): int
    {
        $this->warn('app:sync-frontend đã deprecated. Dùng: php artisan app:publish-frontend');

        return $this->call('app:publish-frontend');
    }
}
