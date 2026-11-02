<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Usuário que realizou a operação
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Identificação do usuário no momento da operação
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();

            // Registro afetado
            $table->string('auditable_type');
            $table->string('auditable_id');

            // created, updated, deleted
            $table->string('event', 30);

            // Valores anteriores e novos
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();

            // Informações da requisição
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['auditable_type', 'auditable_id'],
                'audit_logs_auditable_index'
            );

            $table->index('user_id');
            $table->index('created_at');
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};