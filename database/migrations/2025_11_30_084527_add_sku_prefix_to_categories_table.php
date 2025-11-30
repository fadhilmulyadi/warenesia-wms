<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'sku_prefix')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('sku_prefix')->nullable()->after('image_path');
            });
        }

        $categories = Category::all();
        foreach ($categories as $category) {
            if ($category->sku_prefix) {
                continue;
            }

            $prefix = Category::generatePrefix($category->name);
            $category->sku_prefix = Category::ensureUniquePrefix($prefix, $category->id);
            $category->saveQuietly();
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('sku_prefix')->unique()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'sku_prefix')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['sku_prefix']);
                $table->dropColumn('sku_prefix');
            });
        }
    }
};