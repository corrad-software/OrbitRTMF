<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_scenario_step_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_step_id')->constrained('rtmf_scenario_steps')->cascadeOnDelete();
            $table->unsignedBigInteger('to_step_id')->nullable();
            $table->foreign('to_step_id')->references('id')->on('rtmf_scenario_steps')->nullOnDelete();
            $table->string('condition', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['from_step_id', 'sort_order']);
        });

        Schema::table('rtmf_scenario_steps', function (Blueprint $table) {
            $table->dropForeign(['goes_to_step_id']);
            $table->dropColumn('goes_to_step_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_scenario_step_links');

        Schema::table('rtmf_scenario_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('goes_to_step_id')->nullable();
            $table->foreign('goes_to_step_id')->references('id')->on('rtmf_scenario_steps')->nullOnDelete();
        });
    }
};
