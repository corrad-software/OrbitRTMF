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
            $table->foreignId('sub_module_id')->nullable()->after('module_id')->constrained('rtmf_sub_modules')->nullOnDelete();
        });

        // Backfill: if a sub-module exists whose (module_id, code) matches the frontend's (module_id, tab_code), link it.
        DB::table('rtmf_frontends')
            ->whereNotNull('tab_code')
            ->whereNotNull('module_id')
            ->select('id', 'module_id', 'tab_code')
            ->orderBy('id')
            ->lazy()
            ->each(function ($row) {
                $subId = DB::table('rtmf_sub_modules')
                    ->where('module_id', $row->module_id)
                    ->where('code', $row->tab_code)
                    ->value('id');
                if ($subId) {
                    DB::table('rtmf_frontends')->where('id', $row->id)->update(['sub_module_id' => $subId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropForeign(['sub_module_id']);
            $table->dropColumn('sub_module_id');
        });
    }
};
