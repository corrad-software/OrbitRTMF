<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->dropForeign(['confidence_id']);
            $table->dropColumn('confidence_id');
        });

        Schema::dropIfExists('rtmf_confidences');
    }

    public function down(): void
    {
        Schema::create('rtmf_confidences', function (Blueprint $table) {
            $table->id();
            $table->string('level', 32)->unique();
            $table->string('name', 64);
            $table->string('color', 32)->nullable();
            $table->timestamps();
        });

        Schema::table('rtmf_frontends', function (Blueprint $table) {
            $table->foreignId('confidence_id')->nullable()->constrained('rtmf_confidences')->nullOnDelete();
        });
    }
};
