<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $nasId = DB::table('rtmf_projects')->where('code', 'nas')->value('id');

        foreach (['rtmf_modules', 'rtmf_actors', 'rtmf_scenarios'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('project_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('rtmf_projects')
                    ->nullOnDelete();
            });

            // Back-fill existing records to the NAS project
            DB::table($table)->whereNull('project_id')->update(['project_id' => $nasId]);
        }
    }

    public function down(): void
    {
        foreach (['rtmf_modules', 'rtmf_actors', 'rtmf_scenarios'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign(["{$table}_project_id_foreign"]);
                $t->dropColumn('project_id');
            });
        }
    }
};
