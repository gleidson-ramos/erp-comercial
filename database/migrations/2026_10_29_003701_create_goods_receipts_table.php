<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->restrictOnDelete();

            $table->timestamp('received_at');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['purchase_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};