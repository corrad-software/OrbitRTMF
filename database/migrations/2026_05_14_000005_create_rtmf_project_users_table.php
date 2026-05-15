<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_project_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('rtmf_projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        // Back-fill: add all existing users to the NAS project so no one loses access
        $nasId = DB::table('rtmf_projects')->where('code', 'nas')->value('id');
        if ($nasId) {
            $users = DB::table('users')->pluck('id');
            $now   = now();
            foreach ($users as $userId) {
                DB::table('rtmf_project_users')->insertOrIgnore([
                    'project_id' => $nasId,
                    'user_id'    => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_project_users');
    }
};
