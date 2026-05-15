<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontend_api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtmf_frontend_id')->constrained('rtmf_frontends')->cascadeOnDelete();
            $table->string('method', 16)->default('GET');     // GET | POST | PUT | PATCH | DELETE
            $table->string('endpoint', 255);
            $table->string('description', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rtmf_frontend_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_frontend_api_endpoints');
    }
};
