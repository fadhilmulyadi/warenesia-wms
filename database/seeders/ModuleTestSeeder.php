<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\RestockOrderItem;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ModuleTestSeeder extends Seeder
{
    /**
     * Seed data for all modules except users.
     */
    public function run(): void
    {
        $user = User::query()->first();

        if ($user === null) {
            $this->command?->warn('Seeder dihentikan karena tidak ada user. Tambahkan minimal satu user terlebih dahulu.');

            return;
        }

        $faker = Factory::create('id_ID');

        $units = $this->seedUnits();
        $categories = $this->seedCategories();
        $suppliers = Supplier::factory()->count(8)->create();

        $products = $this->seedProducts($categories, $units, $suppliers);

        $this->seedRestockOrders($user, $suppliers, $products, $faker);
        $this->call(TransactionSeeder::class);
        $this->seedStockAdjustments($user, $products, $faker);
    }

    private function seedUnits(): Collection
    {
        $unitNames = ['PCS', 'BOX', 'PACK', 'SET', 'KG', 'LITER', 'ROLL', 'PAIR'];

        return collect($unitNames)->map(function (string $name) {
            return Unit::firstOrCreate(
                ['name' => $name],
                ['description' => null]
            );
        });
    }

    private function seedCategories(): Collection
    {
        $existingSeedCount = (int) Category::where('name', 'like', 'Seed Category %')->count();
        $categories = collect();

        for ($i = 1; $i <= 8; $i++) {
            $sequence = $existingSeedCount + $i;
            $name = 'Seed Category '.$sequence;
            $basePrefix = 'SC'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $skuPrefix = $this->generateUniqueSkuPrefix($basePrefix);

            $categories->push(
                Category::firstOrCreate(
                    ['name' => $name],
                    [
                        'description' => 'Demo category #'.$sequence,
                        'image_path' => null,
                        'sku_prefix' => $skuPrefix,
                    ]
                )
            );
        }

        return $categories;
    }

    private function generateUniqueSkuPrefix(string $basePrefix): string
    {
        $prefix = $basePrefix;
        $counter = 1;

        while (Category::where('sku_prefix', $prefix)->exists()) {
            $prefix = $basePrefix.$counter;
            $counter++;
        }

        return $prefix;
    }

    private function seedProducts(Collection $categories, Collection $units, Collection $suppliers): Collection
    {
        return Product::factory()
            ->count(150)
            ->make([
                'category_id' => null, // pakai kategori yang sudah ada
                'unit_id' => null, // pakai unit yang sudah ada
            ])
            ->map(function (Product $product) use ($categories, $units, $suppliers) {
                $product->category_id = $categories->random()->id;
                $product->unit_id = $units->random()->id;
                $product->supplier_id = $suppliers->random()->id;
                $product->current_stock = random_int(20, 150);
                $product->min_stock = random_int(5, 25);
                $product->save();

                return $product;
            });
    }

    private function seedRestockOrders(User $user, Collection $suppliers, Collection $products, Generator $faker): void
    {
        $statuses = [
            RestockOrder::STATUS_PENDING,
            RestockOrder::STATUS_CONFIRMED,
            RestockOrder::STATUS_IN_TRANSIT,
            RestockOrder::STATUS_RECEIVED,
            RestockOrder::STATUS_CANCELLED,
        ];

        $existingSeedCount = (int) RestockOrder::where('po_number', 'like', 'PO-SEED-%')->count();

        for ($i = 1; $i <= 12; $i++) {
            $status = $statuses[array_rand($statuses)];
            $orderDate = Carbon::now()->subDays(random_int(0, 40));
            $expectedDate = (clone $orderDate)->addDays(random_int(3, 12));
            $sequence = $existingSeedCount + $i;
            $poNumber = 'PO-SEED-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            $restock = RestockOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $suppliers->random()->id,
                'created_by' => $user->id,
                'confirmed_by' => in_array($status, [
                    RestockOrder::STATUS_CONFIRMED,
                    RestockOrder::STATUS_IN_TRANSIT,
                    RestockOrder::STATUS_RECEIVED,
                ], true) ? $user->id : null,
                'order_date' => $orderDate,
                'expected_delivery_date' => $expectedDate,
                'status' => $status,
                'notes' => 'Generated for demo/testing.',
                'rating' => $status === RestockOrder::STATUS_RECEIVED
                    ? random_int(RestockOrder::MIN_RATING, RestockOrder::MAX_RATING)
                    : null,
                'rating_notes' => $status === RestockOrder::STATUS_RECEIVED ? $faker->sentence() : null,
                'rating_given_by' => $status === RestockOrder::STATUS_RECEIVED ? $user->id : null,
                'rating_given_at' => $status === RestockOrder::STATUS_RECEIVED
                    ? Carbon::now()->subDays(random_int(0, 10))
                    : null,
            ]);

            $items = $products->random(random_int(2, 5));

            foreach ($items as $product) {
                $qty = random_int(10, 60);
                $unitCost = $product->purchase_price;

                RestockOrderItem::create([
                    'restock_order_id' => $restock->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'line_total' => $qty * $unitCost,
                ]);
            }

            $restock->recalculateTotals();
        }
    }

    private function seedStockAdjustments(User $user, Collection $products, Generator $faker): void
    {
        $reasons = [
            'Stock count correction',
            'Damaged goods',
            'Return to supplier',
            'Manual recount',
            'Warehouse move',
        ];

        for ($i = 0; $i < 15; $i++) {
            $product = $products->random()->fresh();

            $before = $product->current_stock;
            $change = random_int(-8, 12);
            $after = max(0, $before + $change);

            $product->update(['current_stock' => $after]);

            StockAdjustment::create([
                'product_id' => $product->id,
                'before_stock' => $before,
                'after_stock' => $after,
                'quantity_change' => $change,
                'reason' => $faker->randomElement($reasons),
                'related_type' => null,
                'related_id' => null,
                'adjusted_by' => $user->id,
            ]);
        }
    }
}
