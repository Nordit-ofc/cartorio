<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * As 8 áreas de serviço mapeadas do Provimento 213/2026 (menu "Serviços" e "Áreas de interesse"
 * do formulário de proposta). Substitui GET /api/v1/interest-areas da API externa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_areas', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('icon', 40)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_areas');
    }
};
