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
        Schema::table('incoming_transactions', function (Blueprint $table) {
            $table->foreignId('restock_order_id')
                ->nullable()
                ->after('id')
                ->constrained('restock_orders')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_transactions', function (Blueprint $table) {
            $table->dropForeign(['restock_order_id']);
            $table->dropColumn('restock_order_id');
        });
    }
};
