<?php

namespace Database\Seeders;

// use App\Models\Category;
// use App\Models\Customer;
// use App\Models\Product;
// use App\Models\Supplier;
// use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo User
        $this->call(RoleAndUserSeeder::class);

        // // Kategori
        // $categories = Category::factory(5)->create();

        // // Supplier
        // $suppliers = Supplier::factory(5)->create();

        // // Customer
        // Customer::factory(10)->create();

        // // Produk
        // $products = Product::factory(30)->make();

        // $products->each(function (Product $product) use ($categories, $suppliers) {
        //     $product->category_id = $categories->random()->id;
        //     $product->supplier_id = $suppliers->random()->id;
        //     $product->current_stock = 0;
        //     $product->save();
        // });

        // $this->call(TransactionSeeder::class);
    }
}
