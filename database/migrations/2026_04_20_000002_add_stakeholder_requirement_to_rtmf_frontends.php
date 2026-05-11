<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->text('stakeholder_requirement')->nullable()->after('business_requirement');
        });
    }

    public function down(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropColumn('stakeholder_requirement');
        });
    }
};
