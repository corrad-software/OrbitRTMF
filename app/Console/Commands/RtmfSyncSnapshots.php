<?php

namespace App\Console\Commands;

use App\Models\RtmfUrlPath;
use App\Services\VueSnapshotService;
use Illuminate\Console\Command;

class RtmfSyncSnapshots extends Command
{
    protected $signature = 'rtmf:sync-snapshots {--id= : Capture a single url-path by id} {--force : Recapture even if already present}';

    protected $description = 'Render static HTML snapshots for RTMF URL paths.';

    public function handle(VueSnapshotService $service): int
    {
        $query = RtmfUrlPath::query();
        if ($id = $this->option('id')) {
            $query->where('id', (int) $id);
        }

        $rows = $query->orderBy('id')->get();

        if ($rows->isEmpty()) {
            $this->warn('No url-paths to sync.');

            return self::SUCCESS;
        }

        $counts = ['ok' => 0, 'not_found' => 0, 'error' => 0, 'skipped' => 0];

        foreach ($rows as $row) {
            if (! $this->option('force') && $row->snapshot_html && $row->snapshot_status === 'ok') {
                $counts['skipped']++;
                continue;
            }

            $result = $service->capture($row->vue_path);
            $meta = $service->extractMetadata($row->vue_path);

            $row->update(array_merge([
                'snapshot_html' => $result['html'],
                'snapshot_status' => $result['status'],
                'snapshot_captured_at' => now(),
            ], $meta));

            $counts[$result['status']] = ($counts[$result['status']] ?? 0) + 1;

            $this->line(sprintf(
                '  %-4s #%-3d %s',
                strtoupper(substr($result['status'], 0, 4)),
                $row->id,
                $row->vue_path ?: '(no path)'
            ));
        }

        $this->newLine();
        $this->table(
            ['ok', 'not_found', 'error', 'skipped'],
            [[$counts['ok'] ?? 0, $counts['not_found'] ?? 0, $counts['error'] ?? 0, $counts['skipped'] ?? 0]]
        );

        return self::SUCCESS;
    }
}
