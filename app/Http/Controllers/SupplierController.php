<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Support\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use DomainException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = Supplier::DEFAULT_PER_PAGE;
    private const MAX_PER_PAGE = 250;
    private const EXPORT_CHUNK_SIZE = 200;

    public function __construct(private readonly SupplierService $suppliers)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        $perPage = $this->resolvePerPage(
            $request,
            self::DEFAULT_PER_PAGE,
            self::MAX_PER_PAGE
        );

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['name', 'average_rating', 'rated_restock_count', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        $filters = [
            'search' => (string) $request->query('q', ''),
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
        ];

        $suppliers = $this->suppliers->index($filters);

        $search = (string) $request->query('q', '');

        return view('suppliers.index', compact('suppliers', 'search', 'sort', 'direction', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Supplier::class);

        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $this->suppliers->create($request->validated());

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
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
    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);

        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierUpdateRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $this->suppliers->update($supplier, $request->validated());

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        try {
            $this->suppliers->delete($supplier);
        } catch (DomainException $exception) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Supplier::class);

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['name', 'average_rating', 'rated_restock_count', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        $supplierQuery = $this->suppliers->query([
            'search' => (string) $request->query('q', ''),
            'sort' => $sort,
            'direction' => $direction,
        ]);
        $fileName = 'suppliers-' . now()->format('Ymd-His') . '.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($supplierQuery): void {
            $output->fputcsv([
                'ID',
                'Name',
                'Contact Person',
                'Email',
                'Phone',
                'Tax Number',
                'Address',
                'City',
                'Country',
                'Is Active',
                'Average Rating',
                'Rated Restock Count',
                'Created At',
                'Updated At',
            ]);

            $supplierQuery
                ->chunk(self::EXPORT_CHUNK_SIZE, static function ($suppliers) use ($output): void {
                    foreach ($suppliers as $supplier) {
                        $output->fputcsv([
                            $supplier->id,
                            $supplier->name,
                            (string) $supplier->contact_person,
                            (string) $supplier->email,
                            (string) $supplier->phone,
                            (string) $supplier->tax_number,
                            (string) $supplier->address,
                            (string) $supplier->city,
                            (string) $supplier->country,
                            $supplier->is_active ? 'Yes' : 'No',
                            $supplier->average_rating !== null ? number_format((float) $supplier->average_rating, 1) : '',
                            (int) ($supplier->rated_restock_count ?? 0),
                            optional($supplier->created_at)->toDateTimeString(),
                            optional($supplier->updated_at)->toDateTimeString(),
                        ]);
                    }
                });
        });
    }
}
