<?php

namespace App\Http\Controllers;

use App\Enums\RestockStatus;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\RestockOrderRatingRequest;
use App\Http\Requests\RestockOrderRequest;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\Supplier;
use App\Services\RestockService;
use App\Support\CsvExporter;
use App\Support\RestockPrefill;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RestockOrderController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = RestockOrder::DEFAULT_PER_PAGE;

    private const MAX_PER_PAGE = 250;

    private const EXPORT_CHUNK_SIZE = 200;

    public function __construct(private readonly RestockService $restockService)
    {
    }

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

        $restockOrdersQuery = $this->restockService->indexQuery([
            'search' => (string) $request->query('q', ''),
            'status' => (array) $request->query('status', []),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $sort,
            'direction' => $direction,
        ], $request->user());

        $restockOrders = $restockOrdersQuery
            ->paginate($perPage)
            ->withQueryString();

        $search = (string) $request->query('q', '');
        $statusOptions = $this->restockStatusFilters();

        return view('restocks.index', compact('restockOrders', 'statusOptions', 'search', 'sort', 'direction', 'perPage'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RestockOrder::class);

        $products = Product::orderBy('name')->get();
        $prefill = RestockPrefill::forCreate($request, $products);

        $suppliers = Supplier::query()
            ->when($prefill['supplier_id'], fn($query, $supplierId) => $query->orWhere('id', $supplierId))
            ->orderBy('name')
            ->get();
        $prefilledSupplierId = $prefill['supplier_id'];

        $redirectUrl = null;
        if ($request->has('product')) {
            $redirectUrl = route('products.show', $request->query('product'));
        }

        return view('restocks.create', [
            'suppliers' => $suppliers,
            'products' => $products,
            'orderDate' => $prefill['order_date'],
            'expectedDeliveryDate' => $prefill['expected_delivery_date'],
            'prefilledSupplierId' => $prefilledSupplierId,
            'initialItems' => $prefill['items'],
            'redirectUrl' => $redirectUrl,
        ]);
    }

    public function store(RestockOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', RestockOrder::class);

        $validated = $request->validated();

        try {
            $restockOrder = $this->restockService->create($validated, $request->user());

            $redirectUrl = $request->input('redirect_to');

            if ($redirectUrl) {
                return redirect($redirectUrl)
                    ->with('success', 'Restock order berhasil ditambahkan.');
            }

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

    public function show(RestockOrder $restock): View
    {
        $this->authorize('view', $restock);

        $restock->load(['supplier', 'createdBy', 'confirmedBy', 'ratingGivenBy', 'items.product', 'incomingTransaction']);
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

        $restockOrdersQuery = $this->restockService->indexQuery([
            'search' => (string) $request->query('q', ''),
            'status' => (array) $request->query('status', []),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $sort,
            'direction' => $direction,
        ], $request->user());
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

        $restockOrdersQuery = $this->restockService->indexQuery([
            'search' => (string) $request->query('q', ''),
            'status' => (array) $request->query('status', []),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $sort,
            'direction' => $direction,
        ], $request->user(), true);

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

        $restock->load(['supplier', 'createdBy', 'confirmedBy', 'items.product', 'incomingTransaction']);
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

    private function restockStatusFilters(): array
    {
        return collect(RestockStatus::cases())
            ->filter(static fn(RestockStatus $status) => in_array($status, [
                RestockStatus::PENDING,
                RestockStatus::CONFIRMED,
                RestockStatus::IN_TRANSIT,
                RestockStatus::RECEIVED,
            ], true))
            ->mapWithKeys(fn(RestockStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    private function abortIfSupplierDoesNotOwn(RestockOrder $restock, int $supplierId): void
    {
        if ((int) $restock->supplier_id !== $supplierId) {
            abort(403);
        }
    }
}
