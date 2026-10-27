<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->string('sku', 50)->unique();
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2);
            $table->decimal('minimum_stock', 12, 3)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
