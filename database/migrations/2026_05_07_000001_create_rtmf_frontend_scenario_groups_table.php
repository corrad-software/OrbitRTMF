<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontend_scenario_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtmf_frontend_id')
                  ->constrained('rtmf_frontends')
                  ->cascadeOnDelete();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rtmf_frontend_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_frontend_scenario_groups');
    }
};
