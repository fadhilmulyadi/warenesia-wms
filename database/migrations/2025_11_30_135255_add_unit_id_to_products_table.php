<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')
                ->nullable()
                ->after('current_stock')
                ->constrained()
                ->restrictOnDelete();
        });

        DB::transaction(function (): void {
            $now = now();
            $products = DB::table('products')->select('id', 'unit')->get();

            foreach ($products as $product) {
                $unitName = trim((string) ($product->unit ?? ''));
                if ($unitName === '') {
                    $unitName = 'pcs';
                }

                $unitId = DB::table('units')
                    ->where('name', $unitName)
                    ->value('id');

                if (!$unitId) {
                    $unitId = DB::table('units')->insertGetId([
                        'name' => $unitName,
                        'description' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['unit_id' => $unitId]);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit', 80)
                ->default('pcs')
                ->after('current_stock');
        });

        DB::transaction(function (): void {
            $unitNames = DB::table('units')
                ->pluck('name', 'id')
                ->toArray();

            $products = DB::table('products')->select('id', 'unit_id')->get();

            foreach ($products as $product) {
                $unitName = $unitNames[$product->unit_id] ?? 'pcs';

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['unit' => $unitName]);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });
    }
};