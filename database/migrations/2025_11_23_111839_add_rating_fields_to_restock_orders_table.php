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
        Schema::table('restock_orders', function (Blueprint $table): void {
            $table->unsignedTinyInteger('rating')
                ->nullable()
                ->after('status');

            $table->text('rating_notes')
                ->nullable()
                ->after('rating');

            $table->foreignId('rating_given_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('rating_notes');

            $table->timestamp('rating_given_at')
                ->nullable()
                ->after('rating_given_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restock_orders', function (Blueprint $table): void {
            $table->dropColumn('rating_given_at');
            $table->dropForeign(['rating_given_by']);
            $table->dropColumn('rating_given_by');
            $table->dropColumn('rating_notes');
            $table->dropColumn('rating');
        });
    }
};
