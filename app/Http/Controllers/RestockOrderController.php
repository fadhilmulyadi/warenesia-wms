<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\RestockOrderRequest;
use App\Http\Requests\RestockOrderRatingRequest;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\Supplier;
use App\Services\RestockService;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use InvalidArgumentException;
use DomainException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RestockOrderController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = RestockOrder::DEFAULT_PER_PAGE;
    private const MAX_PER_PAGE = 250;
    private const EXPORT_CHUNK_SIZE = 200;
    private const ALLOWED_STATUS_FILTERS = [
        RestockOrder::STATUS_PENDING,
        RestockOrder::STATUS_CONFIRMED,
        RestockOrder::STATUS_IN_TRANSIT,
        RestockOrder::STATUS_RECEIVED,
    ];

    public function __construct(private readonly RestockService $restockService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RestockOrder::class);

        $perPage = $this->resolvePerPage(
            $request,
            self::DEFAULT_PER_PAGE,
            self::MAX_PER_PAGE
        );

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['po_number', 'order_date'],
            defaultSort: 'order_date',
            defaultDirection: 'desc'
        );

        $restockOrdersQuery = $this->buildRestockOrderIndexQuery($request, $sort, $direction);

        $restockOrders = $restockOrdersQuery
            ->paginate($perPage)
            ->withQueryString();

        $search = (string) $request->query('q', '');
        $statusOptions = $this->restockStatusFilters();

        return view('restocks.index', compact('restockOrders', 'statusOptions', 'search', 'sort', 'direction', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', RestockOrder::class);

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
        $products = Product::orderBy('name')->get();
        $today = now()->toDateString();

        return view('restocks.create', compact('suppliers', 'products', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RestockOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', RestockOrder::class);

        $validated = $request->validated();

        try {
            $restockOrder = $this->restockService->create($validated, $request->user());

            return redirect()
                ->route('restocks.show', $restockOrder)
                ->with('success', 'Restock order berhasil ditambahkan.');
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' => $exception->getMessage(),
                ]);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'general' => 'Failed to create restock order. Please try again.',
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RestockOrder $restock): View
    {
        $this->authorize('view', $restock);

        $restock->load(['supplier', 'createdBy', 'confirmedBy', 'ratingGivenBy', 'items.product']);
        $statusOptions = RestockOrder::statusOptions();

        return view('restocks.show', compact('restock', 'statusOptions'));
    }

    public function rate(RestockOrderRatingRequest $request, RestockOrder $restockOrder): RedirectResponse
    {
        $this->authorize('rate', $restockOrder);

        if (!$restockOrder->canBeRated()) {
            return redirect()
                ->route('restocks.show', $restockOrder)
                ->withErrors([
                    'general' => 'Only received restock orders can be rated.',
                ]);
        }

        $validated = $request->validated();

        $restockOrder->fill([
            'rating' => $validated['rating'],
            'rating_notes' => $validated['rating_notes'] ?? null,
            'rating_given_by' => $request->user()->id,
            'rating_given_at' => now(),
        ]);

        $restockOrder->save();

        return redirect()
            ->route('restocks.show', $restockOrder)
            ->with('success', 'Rating berhasil disimpan.');
    }

    public function markInTransit(RestockOrder $restock): RedirectResponse
    {
        $this->authorize('markInTransit', $restock);

        try {
            $this->restockService->markInTransit($restock, auth()->user());

            return redirect()
                ->route('restocks.show', $restock)
                ->with('success', 'Restock order berhasil diproses.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('restocks.show', $restock)
                ->withErrors([
                    'general' => $exception->getMessage(),
                ]);
        }
    }

    public function markReceived(RestockOrder $restock): RedirectResponse
    {
        $this->authorize('markReceived', $restock);

        try {
            $this->restockService->markReceived($restock, auth()->user());

            return redirect()
                ->route('restocks.show', $restock)
                ->with('success', 'Restock order berhasil diproses.');
        } catch (DomainException | ModelNotFoundException $exception) {
            return redirect()
                ->route('restocks.show', $restock)
                ->withErrors([
                    'general' => $exception->getMessage(),
                ]);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('restocks.show', $restock)
                ->withErrors([
                    'general' => 'Failed to mark restock as received.',
                ]);
        }
    }

    public function cancel(RestockOrder $restock): RedirectResponse
    {
        $this->authorize('cancel', $restock);

        try {
            $this->restockService->cancel($restock, auth()->user());

            return redirect()
                ->route('restocks.show', $restock)
                ->with('success', 'Restock order berhasil dibatalkan.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('restocks.show', $restock)
                ->withErrors([
                    'general' => $exception->getMessage(),
                ]);
        }
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', RestockOrder::class);

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['po_number', 'order_date'],
            defaultSort: 'order_date',
            defaultDirection: 'desc'
        );

        $restockOrdersQuery = $this->buildRestockOrderIndexQuery($request, $sort, $direction);
        $fileName = 'restocks-' . now()->format('Ymd-His') . '.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($restockOrdersQuery): void {
            $output->fputcsv([
                'PO Number',
                'Order Date',
                'Expected Delivery Date',
                'Supplier',
                'Status',
                'Total Items',
                'Total Quantity',
                'Total Amount',
                'Rating',
                'Rating Notes',
                'Created By',
                'Confirmed By',
                'Created At',
                'Updated At',
            ]);

            $restockOrdersQuery
                ->chunk(self::EXPORT_CHUNK_SIZE, function (Collection $restockOrders) use ($output): void {
                    foreach ($restockOrders as $restockOrder) {
                        $output->fputcsv([
                            $restockOrder->po_number,
                            optional($restockOrder->order_date)->format('Y-m-d'),
                            optional($restockOrder->expected_delivery_date)->format('Y-m-d'),
                            optional($restockOrder->supplier)->name ?? '',
                            $restockOrder->status_label,
                            (int) $restockOrder->total_items,
                            (int) $restockOrder->total_quantity,
                            (float) $restockOrder->total_amount,
                            $restockOrder->rating !== null ? (int) $restockOrder->rating : '',
                            (string) $restockOrder->rating_notes,
                            optional($restockOrder->createdBy)->name ?? '',
                            optional($restockOrder->confirmedBy)->name ?? '',
                            optional($restockOrder->created_at)->toDateTimeString(),
                            optional($restockOrder->updated_at)->toDateTimeString(),
                        ]);
                    }
                });
        });
    }

    public function supplierIndex(Request $request): View
    {
        $this->authorize('viewSupplierRestocks', RestockOrder::class);

        $perPage = $this->resolvePerPage(
            $request,
            self::DEFAULT_PER_PAGE,
            self::MAX_PER_PAGE
        );

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['po_number', 'order_date'],
            defaultSort: 'order_date',
            defaultDirection: 'desc'
        );

        $restockOrdersQuery = $this->buildRestockOrderIndexQuery($request, $sort, $direction);

        $restockOrders = $restockOrdersQuery
            ->paginate($perPage)
            ->withQueryString();

        $statusOptions = $this->restockStatusFilters();
        $search = (string) $request->query('q', '');

        return view('supplier.restocks.index', compact('restockOrders', 'statusOptions', 'search', 'sort', 'direction', 'perPage'));
    }

    public function supplierShow(Request $request, RestockOrder $restock): View
    {
        $this->authorize('viewSupplierRestocks', $restock);

        $this->abortIfSupplierDoesNotOwn($restock, $request->user()->id);

        $restock->load(['supplier', 'createdBy', 'confirmedBy', 'items.product']);
        $statusOptions = RestockOrder::statusOptions();

        return view('supplier.restocks.show', compact('restock', 'statusOptions'));
    }

    public function supplierConfirm(Request $request, RestockOrder $restock): RedirectResponse
    {
        $this->authorize('confirmSupplierRestock', $restock);

        $this->abortIfSupplierDoesNotOwn($restock, $request->user()->id);

        try {
            $this->restockService->supplierConfirm($restock, $request->user());

            return redirect()
                ->route('supplier.restocks.show', $restock)
                ->with('success', 'Restock order berhasil disetujui.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('supplier.restocks.show', $restock)
                ->withErrors(['general' => $exception->getMessage()]);
        }
    }

    public function supplierReject(Request $request, RestockOrder $restock): RedirectResponse
    {
        $this->authorize('rejectSupplierRestock', $restock);

        $this->abortIfSupplierDoesNotOwn($restock, $request->user()->id);

        try {
            $this->restockService->supplierReject($restock, $request->user(), $request->input('reject_reason'));

            return redirect()
                ->route('supplier.restocks.show', $restock)
                ->with('success', 'Restock order berhasil ditolak.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('supplier.restocks.show', $restock)
                ->withErrors(['general' => $exception->getMessage()]);
        }
    }

    private function buildRestockOrderIndexQuery(
        Request $request,
        string $sort,
        string $direction
    ): Builder {
        $search = (string) $request->query('q', '');

        $query = RestockOrder::query()
            ->with(['supplier', 'createdBy', 'confirmedBy', 'ratingGivenBy']);

        if ($request->routeIs('supplier.restocks.*') && $request->user() !== null) {
            $query->where('supplier_id', $request->user()->id);
        }

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search): void {
                $this->applySearch($searchQuery, $search, ['po_number']);

                $searchQuery->orWhereHas('supplier', function (Builder $supplierQuery) use ($search): void {
                    $supplierQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $this->applyFilters($query, $request, [
            'status' => function (Builder $statusQuery, $value): void {
                $statuses = array_values(array_intersect(
                    (array) $value,
                    self::ALLOWED_STATUS_FILTERS
                ));

                if (count($statuses) === 0) {
                    return;
                }

                $statusQuery->whereIn('status', $statuses);
            },
        ]);

        $this->applyDateRange($query, $request, 'order_date');

        $query->orderBy($sort, $direction)
            ->orderBy('id');

        return $query;
    }

    private function restockStatusFilters(): array
    {
        return array_intersect_key(
            RestockOrder::statusOptions(),
            array_flip(self::ALLOWED_STATUS_FILTERS)
        );
    }

    private function abortIfSupplierDoesNotOwn(RestockOrder $restock, int $supplierId): void
    {
        if ($restock->supplier_id !== $supplierId) {
            abort(403);
        }
    }
}
