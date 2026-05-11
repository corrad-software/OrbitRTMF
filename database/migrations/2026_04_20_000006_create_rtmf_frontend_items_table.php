<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtmf_frontend_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtmf_frontend_id')->constrained('rtmf_frontends')->cascadeOnDelete();
            $table->string('id_fr', 32)->nullable();
            $table->string('type', 32)->nullable();          // Text | Button | Component
            $table->string('label', 255)->nullable();
            $table->string('condition', 255)->nullable();
            $table->string('screen_name', 128)->nullable();
            $table->string('table_fieldname', 255)->nullable();
            $table->string('status', 32)->nullable();        // implemented | partial | missing
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rtmf_frontend_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtmf_frontend_items');
    }
};
