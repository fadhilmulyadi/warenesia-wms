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
        Schema::create('incoming_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_transaction_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_transaction_items');
    }
};
