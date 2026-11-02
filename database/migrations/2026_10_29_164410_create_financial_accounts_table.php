<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();

            // RECEBER ou PAGAR
            $table->string('type', 10);

            // Referência opcional à origem da obrigação
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained('sales')
                ->restrictOnDelete();

            $table->foreignId('purchase_id')
                ->nullable()
                ->constrained('purchases')
                ->restrictOnDelete();

            $table->string('description', 200);
            $table->string('document_number', 50)->nullable();

            $table->date('issue_date');
            $table->date('due_date');

            $table->decimal('amount', 14, 2);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('interest', 14, 2)->default(0);

            // ABERTA, PARCIAL, PAGA ou CANCELADA
            $table->string('status', 20)->default('ABERTA');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['type', 'status', 'due_date']);
            $table->index(['customer_id', 'status']);
            $table->index(['supplier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};