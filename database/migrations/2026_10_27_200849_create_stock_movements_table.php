<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->string('type', 20);

            $table->decimal('quantity', 14, 3);

            $table->decimal('unit_cost', 12, 2)->nullable();

            $table->string('reason', 100);

            $table->text('notes')->nullable();

            $table->decimal('balance_after', 14, 3);

            $table->timestamp('moved_at');

            $table->timestamps();

            $table->index(['product_id', 'moved_at']);
            $table->index(['type', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};