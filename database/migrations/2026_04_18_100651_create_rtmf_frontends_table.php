<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontends', function (Blueprint $table) {
            $table->id();
            $table->string('spec_id', 64)->unique();
            $table->string('module_code', 4);
            $table->string('tab_code', 64)->nullable();
            $table->string('title');
            $table->string('vue_path', 512)->nullable();
            $table->string('actor', 128)->nullable();
            $table->text('business_requirement')->nullable();
            $table->string('confidence', 16)->default('unknown');
            $table->longText('description')->nullable();
            $table->integer('line_count')->nullable();
            $table->integer('file_size_kb')->nullable();
            $table->json('shared_components')->nullable();
            $table->json('sections')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('module_code');
            $table->index('tab_code');
            $table->index(['module_code', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_frontends');
    }
};
