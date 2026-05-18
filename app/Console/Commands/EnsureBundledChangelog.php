<?php

namespace App\Console\Commands;

use App\Services\SettingService;
use Illuminate\Console\Command;

class EnsureBundledChangelog extends Command
{
    protected $signature = 'changelog:ensure {--force : Overwrite settings even when changelog key exists}';

    protected $description = 'Seed settings.changelog from bundled CHANGELOG.md when empty (deploy-safe).';

    public function handle(SettingService $settings): int
    {
        $existing = trim((string) $settings->get('changelog', ''));
        if ($existing !== '' && $existing !== 'null' && ! $this->option('force')) {
            $this->info('Changelog already present in settings.');

            return self::SUCCESS;
        }

        foreach ([
            base_path('CHANGELOG.md'),
            base_path('docs/CHANGELOG.md'),
        ] as $path) {
            if (! is_file($path) || ! is_readable($path)) {
                continue;
            }

            $content = (string) file_get_contents($path);
            if (trim($content) === '') {
                continue;
            }

            $settings->set('changelog', $content);
            $this->info('Changelog seeded from: '.$path);

            return self::SUCCESS;
        }

        $this->error('No bundled CHANGELOG.md found in the container.');

        return self::FAILURE;
    }
}
