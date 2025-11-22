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
        Schema::create('restock_orders', function (Blueprint $table) {
            $table->id();

            $table->string('po_number')->unique();

            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();

            $table->string('status', 30)->default('pending');

            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('total_quantity')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_orders');
    }
};
