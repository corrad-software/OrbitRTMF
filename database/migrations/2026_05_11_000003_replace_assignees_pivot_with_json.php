<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('rtmf_frontend_assignees');

        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->json('assignees')->nullable()->after('is_done');
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropColumn('assignees');
        });

        Schema::create('rtmf_frontend_assignees', function (Blueprint $table) {
            $table->foreignId('rtmf_frontend_id')->constrained('rtmf_frontends')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['rtmf_frontend_id', 'user_id']);
        });
    }
};
