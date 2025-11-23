<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    private const EXPORT_CHUNK_SIZE = 200;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $suppliersQuery = $this->buildSupplierIndexQuery($request);

        $suppliers = $suppliersQuery->paginate(Supplier::DEFAULT_PER_PAGE)
            ->withQueryString();

        $search = (string) $request->query('q', '');

        return view('admin.suppliers.index', compact('suppliers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
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
        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->products()->exists()) {
            return redirect()
                ->route('admin.suppliers.index')
                ->with('error', 'Supplier cannot be deleted because it is used by one or more products.');
        }

        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $supplierQuery = $this->buildSupplierIndexQuery($request);
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
                ->orderBy('name')
                ->orderBy('id')
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

    private function buildSupplierIndexQuery(Request $request): Builder
    {
        $search = (string) $request->query('q', '');

        return Supplier::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('contact_person', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->withCount([
                'restockOrders as rated_restock_count' => function ($query): void {
                    $query->whereNotNull('rating');
                },
            ])
            ->withAvg(
                'restockOrders as average_rating',
                'rating'
            )
            ->orderBy('name');
    }
}
