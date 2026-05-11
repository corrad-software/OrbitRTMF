<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Seed lookup tables with canonical rows + any distinct values currently on rtmf_frontends.
        $this->seedModules();
        $this->seedConfidences();
        $this->seedActors();
        $this->seedUrlPaths();

        // 2. Add FK columns (nullable first so backfill can run).
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->after('spec_id')->constrained('rtmf_modules')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->after('module_id')->constrained('rtmf_actors')->nullOnDelete();
            $table->foreignId('url_path_id')->nullable()->after('actor_id')->constrained('rtmf_url_paths')->nullOnDelete();
            $table->foreignId('confidence_id')->nullable()->after('url_path_id')->constrained('rtmf_confidences')->nullOnDelete();
        });

        // 3. Backfill FKs on each row.
        $this->backfillFks();

        // 4. Drop old columns.
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $existingIndexes = collect(Schema::getIndexes('rtmf_frontends'))->pluck('name')->unique();
            foreach (['rtmf_frontends_module_code_index', 'rtmf_frontends_module_code_sort_order_index'] as $idx) {
                if ($existingIndexes->contains($idx)) {
                    $table->dropIndex($idx);
                }
            }
            $table->dropColumn([
                'module_code',
                'actor',
                'vue_path',
                'live_url',
                'confidence',
                'line_count',
                'file_size_kb',
                'shared_components',
                'snapshot_html',
                'snapshot_captured_at',
                'snapshot_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropForeign(['actor_id']);
            $table->dropForeign(['url_path_id']);
            $table->dropForeign(['confidence_id']);
            $table->dropColumn(['module_id', 'actor_id', 'url_path_id', 'confidence_id']);

            // Restore dropped columns (empty — down() is a best-effort escape hatch).
            $table->string('module_code', 4)->nullable();
            $table->string('actor', 128)->nullable();
            $table->string('vue_path', 512)->nullable();
            $table->string('live_url', 1024)->nullable();
            $table->string('confidence', 16)->default('unknown');
            $table->integer('line_count')->nullable();
            $table->integer('file_size_kb')->nullable();
            $table->json('shared_components')->nullable();
            $table->longText('snapshot_html')->nullable();
            $table->timestamp('snapshot_captured_at')->nullable();
            $table->string('snapshot_status', 16)->nullable();
        });
    }

    private function seedModules(): void
    {
        $now = now();
        $defaults = [
            ['code' => 'QS', 'name' => 'Pendaftaran Pantas Perseorangan', 'description' => 'Individual quick registration (QS).', 'sort_order' => 10],
            ['code' => 'QB', 'name' => 'Pendaftaran Pantas Pukal', 'description' => 'Bulk / disaster-relief quick registration (QB).', 'sort_order' => 20],
            ['code' => 'FT', 'name' => 'Pendaftaran Lengkap', 'description' => 'Full registration with family tree (FT).', 'sort_order' => 30],
        ];
        foreach ($defaults as $row) {
            DB::table('rtmf_modules')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now]),
            );
        }
    }

    private function seedConfidences(): void
    {
        $now = now();
        $defaults = [
            ['level' => 'high', 'name' => 'High', 'color' => 'emerald', 'description' => 'Explicit label match.', 'sort_order' => 10],
            ['level' => 'medium', 'name' => 'Medium', 'color' => 'amber', 'description' => 'Structural or contextual match.', 'sort_order' => 20],
            ['level' => 'low', 'name' => 'Low', 'color' => 'rose', 'description' => 'Best guess only.', 'sort_order' => 30],
            ['level' => 'unknown', 'name' => 'Unknown', 'color' => 'slate', 'description' => 'Not yet assessed.', 'sort_order' => 40],
        ];
        foreach ($defaults as $row) {
            DB::table('rtmf_confidences')->updateOrInsert(
                ['level' => $row['level']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now]),
            );
        }
    }

    private function seedActors(): void
    {
        $now = now();
        $names = DB::table('rtmf_frontends')
            ->whereNotNull('actor')
            ->distinct()
            ->pluck('actor')
            ->filter()
            ->values();

        foreach ($names as $i => $name) {
            DB::table('rtmf_actors')->updateOrInsert(
                ['name' => $name],
                ['sort_order' => ($i + 1) * 10, 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }

    private function seedUrlPaths(): void
    {
        $now = now();
        $paths = DB::table('rtmf_frontends')
            ->select('vue_path', 'live_url', 'line_count', 'file_size_kb', 'shared_components', 'snapshot_html', 'snapshot_captured_at', 'snapshot_status')
            ->whereNotNull('vue_path')
            ->get()
            ->groupBy('vue_path');

        foreach ($paths as $vuePath => $rows) {
            $first = $rows->first();
            DB::table('rtmf_url_paths')->updateOrInsert(
                ['vue_path' => $vuePath],
                [
                    'live_url' => $first->live_url,
                    'line_count' => $first->line_count,
                    'file_size_kb' => $first->file_size_kb,
                    'shared_components' => $first->shared_components,
                    'snapshot_html' => $first->snapshot_html,
                    'snapshot_captured_at' => $first->snapshot_captured_at,
                    'snapshot_status' => $first->snapshot_status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    private function backfillFks(): void
    {
        $moduleIds = DB::table('rtmf_modules')->pluck('id', 'code');
        $confidenceIds = DB::table('rtmf_confidences')->pluck('id', 'level');
        $actorIds = DB::table('rtmf_actors')->pluck('id', 'name');
        $urlPathIds = DB::table('rtmf_url_paths')->whereNotNull('vue_path')->pluck('id', 'vue_path');

        DB::table('rtmf_frontends')->orderBy('id')->lazy()->each(function ($row) use ($moduleIds, $confidenceIds, $actorIds, $urlPathIds) {
            DB::table('rtmf_frontends')->where('id', $row->id)->update([
                'module_id' => $row->module_code ? ($moduleIds[$row->module_code] ?? null) : null,
                'actor_id' => $row->actor ? ($actorIds[$row->actor] ?? null) : null,
                'url_path_id' => $row->vue_path ? ($urlPathIds[$row->vue_path] ?? null) : null,
                'confidence_id' => $row->confidence ? ($confidenceIds[$row->confidence] ?? null) : null,
            ]);
        });
    }
};
