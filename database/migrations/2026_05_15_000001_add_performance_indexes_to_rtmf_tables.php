<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_modules', function (Blueprint $table) {
            $table->index('project_id');
        });

        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->index('module_id');
            $table->index('is_done');
        });

        Schema::table('rtmf_actors', function (Blueprint $table) {
            $table->index('project_id');
        });

        Schema::table('rtmf_frontend_actor', function (Blueprint $table) {
            $table->index('rtmf_frontend_id');
            $table->index('rtmf_actor_id');
        });

        Schema::table('rtmf_frontend_items', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_modules', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
        });

        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropIndex(['module_id']);
            $table->dropIndex(['is_done']);
        });

        Schema::table('rtmf_actors', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
        });

        Schema::table('rtmf_frontend_actor', function (Blueprint $table) {
            $table->dropIndex(['rtmf_frontend_id']);
            $table->dropIndex(['rtmf_actor_id']);
        });

        Schema::table('rtmf_frontend_items', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
