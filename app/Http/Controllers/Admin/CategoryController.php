<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    private const DEFAULT_PER_PAGE = 10;
    private const EXPORT_CHUNK_SIZE = 200;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categoryQuery = $this->buildCategoryIndexQuery($request);

        $categories = $categoryQuery
            ->paginate(self::DEFAULT_PER_PAGE)
            ->withQueryString();

        $search = (string) $request->query('q', '');

        return view('admin.categories.index', [
            'categories' => $categories,
            'search'     => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
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
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh satu atau lebih produk.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function quickStore(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'id'   => $category->id,
                'name' => $category->name,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Category created successfully.')
            ->with('newCategoryId', $category->id);
    }

    public function export(Request $request): StreamedResponse
    {
        $categoryQuery = $this->buildCategoryIndexQuery($request);
        $fileName = 'categories-' . now()->format('Ymd-His') . '.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($categoryQuery): void {
            $output->fputcsv([
                'ID',
                'Name',
                'Description',
                'Created At',
                'Updated At',
            ]);

            $categoryQuery
                ->orderBy('name')
                ->orderBy('id')
                ->chunk(self::EXPORT_CHUNK_SIZE, static function ($categories) use ($output): void {
                    foreach ($categories as $category) {
                        $output->fputcsv([
                            $category->id,
                            $category->name,
                            (string) $category->description,
                            optional($category->created_at)->toDateTimeString(),
                            optional($category->updated_at)->toDateTimeString(),
                        ]);
                    }
                });
        });
    }

    private function buildCategoryIndexQuery(Request $request): Builder
    {
        $search = (string) $request->input('q', '');

        return Category::query()
            ->withCount('products')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name');
    }

}
