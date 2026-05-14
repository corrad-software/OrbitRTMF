<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontend_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtmf_frontend_id')->constrained('rtmf_frontends')->cascadeOnDelete();
            $table->string('role'); // business_analyst | qa | technical
            $table->boolean('is_checked')->default(false);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['rtmf_frontend_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_frontend_feedbacks');
    }
};
