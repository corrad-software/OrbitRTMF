<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_url_paths', function (Blueprint $table) {
            $table->id();
            $table->string('vue_path', 512)->nullable()->unique();
            $table->string('live_url', 1024)->nullable();
            $table->text('description')->nullable();
            $table->integer('line_count')->nullable();
            $table->integer('file_size_kb')->nullable();
            $table->json('shared_components')->nullable();
            $table->longText('snapshot_html')->nullable();
            $table->timestamp('snapshot_captured_at')->nullable();
            $table->string('snapshot_status', 16)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_url_paths');
    }
};
