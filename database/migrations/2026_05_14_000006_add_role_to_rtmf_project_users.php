<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_project_users', function (Blueprint $table) {
            $table->string('role', 32)->default('viewer')->after('user_id');
        });

        // Existing members were added before roles existed — treat them as admins
        DB::table('rtmf_project_users')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('rtmf_project_users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
