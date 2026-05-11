<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->string('vue_path', 512)->nullable()->after('tab_code');
            $table->string('live_url', 1024)->nullable()->after('vue_path');
            $table->longText('snapshot_html')->nullable()->after('live_url');
            $table->string('snapshot_status', 16)->nullable()->after('snapshot_html');
            $table->timestamp('snapshot_captured_at')->nullable()->after('snapshot_status');
        });

        DB::table('rtmf_frontends')->whereNotNull('url_path_id')->orderBy('id')->lazy()->each(function ($row) {
            $path = DB::table('rtmf_url_paths')->where('id', $row->url_path_id)->first();
            if (! $path) {
                return;
            }
            DB::table('rtmf_frontends')->where('id', $row->id)->update([
                'vue_path'             => $path->vue_path,
                'live_url'             => $path->live_url,
                'snapshot_html'        => $path->snapshot_html,
                'snapshot_status'      => $path->snapshot_status,
                'snapshot_captured_at' => $path->snapshot_captured_at,
            ]);
        });

        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropForeign(['url_path_id']);
            $table->dropColumn('url_path_id');
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->foreignId('url_path_id')->nullable()->constrained('rtmf_url_paths')->nullOnDelete();
            $table->dropColumn(['vue_path', 'live_url', 'snapshot_html', 'snapshot_status', 'snapshot_captured_at']);
        });
    }
};
