<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->longText('snapshot_html')->nullable()->after('sections');
            $table->timestamp('snapshot_captured_at')->nullable()->after('snapshot_html');
            $table->string('snapshot_status', 16)->nullable()->after('snapshot_captured_at');
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropColumn(['snapshot_html', 'snapshot_captured_at', 'snapshot_status']);
        });
    }
};
