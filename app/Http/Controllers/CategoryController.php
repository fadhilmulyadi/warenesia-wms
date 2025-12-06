<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Services\CategoryService;
use App\Support\CsvExporter;
use DomainException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = 10;

    private const MAX_PER_PAGE = 250;

    private const EXPORT_CHUNK_SIZE = 200;

    private const NAME_FILTER_THRESHOLD = 8;

    public function __construct(private readonly CategoryService $categories) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

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

        $filters = [
            'search' => (string) $request->query('q', ''),
            'name' => $request->query('name'),
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
        ];

        $categories = $this->categories->index($filters);

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
    public function store(CategoryStoreRequest $request)
    {
        $this->authorize('create', Category::class);

        $this->categories->create($request->validated());

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
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $this->categories->update($category, $request->validated());

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

        try {
            $this->categories->delete($category);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function quickStore(CategoryStoreRequest $request)
    {
        $this->authorize('create', Category::class);

        $category = $this->categories->create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $category->id,
                'name' => $category->name,
                'sku_prefix' => $category->sku_prefix,
            ], 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil ditambahkan.')
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

        $categoryQuery = $this->categories->query([
            'search' => (string) $request->query('q', ''),
            'sort' => $sort,
            'direction' => $direction,
            'name' => $request->query('name'),
        ]);
        $fileName = 'categories-'.now()->format('Ymd-His').'.csv';

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
}
