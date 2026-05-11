<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontend_scenario_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtmf_frontend_scenario_group_id')
                  ->constrained('rtmf_frontend_scenario_groups')
                  ->cascadeOnDelete();
            $table->string('step', 32)->nullable();
            $table->string('fasa', 128)->nullable();
            $table->string('role', 128)->nullable();
            $table->text('aktiviti')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rtmf_frontend_scenario_group_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_frontend_scenario_rows');
    }
};
