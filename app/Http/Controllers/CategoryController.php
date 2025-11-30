<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 250;
    private const EXPORT_CHUNK_SIZE = 200;
    private const NAME_FILTER_THRESHOLD = 8;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $this->resolvePerPage(
            $request,
            self::DEFAULT_PER_PAGE,
            self::MAX_PER_PAGE
        );

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['name', 'sku_prefix', 'products_count', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        $categoriesQuery = $this->buildCategoryIndexQuery($request, $sort, $direction);

        $categories = $categoriesQuery
            ->paginate($perPage)
            ->withQueryString();

        $search = (string) $request->query('q', '');
        $nameFilterOptions = Category::orderBy('name')->get(['id', 'name']);
        $showNameFilter = $nameFilterOptions->count() >= self::NAME_FILTER_THRESHOLD;

        return view('categories.index', compact(
            'categories',
            'search',
            'sort',
            'direction',
            'perPage',
            'nameFilterOptions',
            'showNameFilter'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Category::class);

        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $this->authorize('create', Category::class);

        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('categories', 'public');
        } else {
            unset($data['image_path']);
        }

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            return back()
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh satu atau lebih produk.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function quickStore(CategoryRequest $request)
    {
        $this->authorize('create', Category::class);

        $data = $request->validated();

        $category = Category::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'id'         => $category->id,
                'name'       => $category->name,
                'sku_prefix' => $category->sku_prefix,
            ], 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Category created successfully.')
            ->with('newCategoryId', $category->id);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Category::class);

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['name', 'sku_prefix', 'products_count', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        $categoryQuery = $this->buildCategoryIndexQuery($request, $sort, $direction);
        $fileName = 'categories-' . now()->format('Ymd-His') . '.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($categoryQuery): void {
            $output->fputcsv([
                'ID',
                'Name',
                'SKU Prefix',
                'Description',
                'Created At',
                'Updated At',
            ]);

            $categoryQuery
                ->chunk(self::EXPORT_CHUNK_SIZE, static function ($categories) use ($output): void {
                    foreach ($categories as $category) {
                        $output->fputcsv([
                            $category->id,
                            $category->name,
                            $category->sku_prefix,
                            (string) $category->description,
                            optional($category->created_at)->toDateTimeString(),
                            optional($category->updated_at)->toDateTimeString(),
                        ]);
                    }
                });
        });
    }

    private function buildCategoryIndexQuery(
        Request $request,
        string $sort,
        string $direction
    ): Builder {
        $search = (string) $request->query('q', '');

        $query = Category::query()
            ->withCount('products');

        $this->applySearch($query, $search, ['name', 'description', 'sku_prefix']);

        $this->applyFilters($query, $request, [
            'name' => 'name',
        ]);

        $query->orderBy($sort, $direction)
            ->orderBy('id');

        return $query;
    }

}
