<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rtmf_scenario_steps', function (Blueprint $table) {
            $table->dropForeign(['rtmf_actor_id']);
            $table->dropColumn('rtmf_actor_id');
        });

        Schema::create('rtmf_scenario_step_actor', function (Blueprint $table) {
            $table->foreignId('rtmf_scenario_step_id')->constrained('rtmf_scenario_steps')->cascadeOnDelete();
            $table->foreignId('rtmf_actor_id')->constrained('rtmf_actors')->cascadeOnDelete();
            $table->primary(['rtmf_scenario_step_id', 'rtmf_actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_scenario_step_actor');

        Schema::table('rtmf_scenario_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('rtmf_actor_id')->nullable()->after('rtmf_frontend_id');
            $table->foreign('rtmf_actor_id')->references('id')->on('rtmf_actors')->nullOnDelete();
        });
    }
};
