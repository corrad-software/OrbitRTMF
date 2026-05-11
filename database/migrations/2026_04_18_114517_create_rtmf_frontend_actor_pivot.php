<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontend_actor', function (Blueprint $table) {
            $table->foreignId('rtmf_frontend_id')->constrained('rtmf_frontends')->cascadeOnDelete();
            $table->foreignId('rtmf_actor_id')->constrained('rtmf_actors')->cascadeOnDelete();
            $table->primary(['rtmf_frontend_id', 'rtmf_actor_id']);
        });

        // Backfill from existing actor_id column.
        DB::table('rtmf_frontends')
            ->whereNotNull('actor_id')
            ->select('id', 'actor_id')
            ->orderBy('id')
            ->lazy()
            ->each(function ($row) {
                DB::table('rtmf_frontend_actor')->insertOrIgnore([
                    'rtmf_frontend_id' => $row->id,
                    'rtmf_actor_id' => $row->actor_id,
                ]);
            });

        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropForeign(['actor_id']);
            $table->dropColumn('actor_id');
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->foreignId('actor_id')->nullable()->constrained('rtmf_actors')->nullOnDelete();
        });

        Schema::dropIfExists('rtmf_frontend_actor');
    }
};
