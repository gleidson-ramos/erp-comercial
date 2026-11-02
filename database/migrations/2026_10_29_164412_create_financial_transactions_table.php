<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_account_id')
                ->constrained('financial_accounts')
                ->restrictOnDelete();

            // PAGAMENTO ou RECEBIMENTO
            $table->string('type', 15);

            $table->date('transaction_date');
            $table->decimal('amount', 14, 2);

            // DINHEIRO, PIX, CARTAO, TRANSFERENCIA ou OUTRO
            $table->string('payment_method', 20)->default('OUTRO');

            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['financial_account_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};