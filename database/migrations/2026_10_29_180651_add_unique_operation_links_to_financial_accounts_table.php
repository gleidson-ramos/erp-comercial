<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->unique(
                'sale_id',
                'financial_accounts_sale_id_unique'
            );

            $table->unique(
                'purchase_id',
                'financial_accounts_purchase_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropUnique(
                'financial_accounts_sale_id_unique'
            );

            $table->dropUnique(
                'financial_accounts_purchase_id_unique'
            );
        });
    }
};