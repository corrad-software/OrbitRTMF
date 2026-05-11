<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->renameColumn('live_url', 'url_dev');
            $table->string('url_stg', 1024)->nullable()->after('url_dev');
            $table->string('url_prd', 1024)->nullable()->after('url_stg');
            $table->dropColumn(['snapshot_html', 'snapshot_status', 'snapshot_captured_at']);
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->renameColumn('url_dev', 'live_url');
            $table->dropColumn(['url_stg', 'url_prd']);
            $table->longText('snapshot_html')->nullable();
            $table->string('snapshot_status', 16)->nullable();
            $table->timestamp('snapshot_captured_at')->nullable();
        });
    }
};
