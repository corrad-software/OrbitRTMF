<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontend_links', function (Blueprint $table) {
            $table->foreignId('from_frontend_id')->constrained('rtmf_frontends')->cascadeOnDelete();
            $table->foreignId('to_frontend_id')->constrained('rtmf_frontends')->cascadeOnDelete();
            $table->primary(['from_frontend_id', 'to_frontend_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_frontend_links');
    }
};
