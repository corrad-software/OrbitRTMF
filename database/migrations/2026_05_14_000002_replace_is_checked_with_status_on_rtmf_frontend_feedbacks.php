<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_frontend_feedbacks', function (Blueprint $table) {
            $table->dropColumn('is_checked');
            $table->string('status')->default('open')->after('role'); // open | reviewed | approved
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontend_feedbacks', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->boolean('is_checked')->default(false)->after('role');
        });
    }
};
