<?php

namespace App\Console\Commands;

use App\Models\Word;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class EnsureSeededCommand extends Command
{
    protected $signature = 'app:ensure-seeded';

    protected $description = 'Seed database only when empty (fast restarts on Render)';

    public function handle(): int
    {
        if (! Schema::hasTable('words')) {
            $this->warn('words table missing — run migrate first.');

            return self::FAILURE;
        }

        if (Word::count() >= 100) {
            $this->info('Data already present, skip seed.');

            return self::SUCCESS;
        }

        $this->info('Empty database — seeding...');
        $this->call('db:seed', ['--force' => true]);
        $this->call('app:enrich-vietnamese');

        return self::SUCCESS;
    }
}
