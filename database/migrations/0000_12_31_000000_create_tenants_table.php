<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant = instituição cliente da nordit (cartório). Banco único, isolamento por
 * tenant_id (ADR-006). Criada antes de users porque users.tenant_id aponta para cá.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('institution_type', 20);
            $table->string('cnpj', 14)->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone', 11)->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
