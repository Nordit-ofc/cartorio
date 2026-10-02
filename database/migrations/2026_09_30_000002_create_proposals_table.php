<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitações de proposta da landing (SPEC-005). São leads da nordit, não dados de uma
 * instituição: a tabela é global (sem tenant_id) e só o super_admin as vê (ADR-006).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('protocol', 20)->unique();
            $table->string('contact_name', 120);
            $table->string('organization', 160);
            $table->string('institution_type', 20);
            $table->string('email', 160)->index();
            $table->string('phone', 11)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->index();
            $table->string('source', 10)->default('form');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
        });

        Schema::create('interest_area_proposal', function (Blueprint $table): void {
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interest_area_id')->constrained()->restrictOnDelete();
            $table->primary(['proposal_id', 'interest_area_id']);
        });

        // Histórico de status: alimenta a linha do tempo da consulta pública e a tela do sysadmin.
        Schema::create('proposal_status_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->string('note', 1000)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_status_changes');
        Schema::dropIfExists('interest_area_proposal');
        Schema::dropIfExists('proposals');
    }
};
