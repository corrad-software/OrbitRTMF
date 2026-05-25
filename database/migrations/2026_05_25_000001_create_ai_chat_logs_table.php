<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('question');
            $table->text('response');
            $table->string('model', 64)->default('claude-haiku-4-5-20251001');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 8)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
    }
};
