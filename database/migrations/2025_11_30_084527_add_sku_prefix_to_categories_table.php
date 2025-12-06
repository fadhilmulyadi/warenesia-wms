<?php

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'sku_prefix')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('sku_prefix')->nullable()->after('image_path');
            });
        }

        $service = app(CategoryService::class);

        Category::query()
            ->whereNull('sku_prefix')
            ->each(static function (Category $category) use ($service): void {
                $prefix = $service->generatePrefix($category->name, $category->id);
                $category->forceFill(['sku_prefix' => $prefix])->saveQuietly();
            });

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
