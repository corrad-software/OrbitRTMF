<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_scenario_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtmf_scenario_id')->constrained('rtmf_scenarios')->cascadeOnDelete();
            $table->unsignedBigInteger('rtmf_frontend_id')->nullable();
            $table->foreign('rtmf_frontend_id')->references('id')->on('rtmf_frontends')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('goes_to_step_id')->nullable();
            $table->foreign('goes_to_step_id')->references('id')->on('rtmf_scenario_steps')->nullOnDelete();
            $table->timestamps();
            $table->index(['rtmf_scenario_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_scenario_steps');
    }
};
