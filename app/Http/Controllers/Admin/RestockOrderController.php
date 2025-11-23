<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestockOrderRequest;
use App\Http\Requests\RestockOrderRatingRequest;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\RestockOrderItem;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RestockOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $statusFilter = (string) $request->query('status', '');

        $restockOrdersQuery = RestockOrder::query()
            ->with(['supplier', 'createdBy'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('po_number', 'like', '%' . $search . '%')
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search): void {
                            $supplierQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($statusFilter !== '', function ($query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        $restockOrders = $restockOrdersQuery
            ->paginate(RestockOrder::DEFAULT_PER_PAGE)
            ->withQueryString();

        $statusOptions = RestockOrder::statusOptions();

        return view('admin.restocks.index', compact('restockOrders', 'statusOptions', 'search', 'statusFilter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
        $products = Product::orderBy('name')->get();
        $today = now()->toDateString();

        return view('admin.restocks.create', compact('suppliers', 'products', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RestockOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $itemsData = $validated['items'] ?? [];

        if (count($itemsData) === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' => 'At least one product must be added to the restock order.',
                ]);
        }

        $purchaseOrderNumber = RestockOrder::generateNextPurchaseOrderNumber();

        DB::beginTransaction();

        try {
            $totalItems = count($itemsData);
            $totalQuantity = 0;
            $totalAmount = 0.0;

            $restockOrder = RestockOrder::create([
                'po_number' => $purchaseOrderNumber,
                'supplier_id' => $validated['supplier_id'],
                'created_by' => $request->user()->id,
                'confirmed_by' => null,
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => RestockOrder::STATUS_PENDING,
                'total_items' => 0,
                'total_quantity' => 0,
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $quantity = (int) $itemData['quantity'];
                $unitCost = isset($itemData['unit_cost'])
                    ? (float) $itemData['unit_cost']
                    : 0.0;

                $lineTotal = $quantity * $unitCost;

                $totalQuantity += $quantity;
                $totalAmount += $lineTotal;

                RestockOrderItem::create([
                    'restock_order_id' => $restockOrder->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);
            }

            $restockOrder->update([
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.restocks.show', $restockOrder)
                ->with('success', 'Restock order created successfully. Waiting for supplier confirmation.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to create restock order', [
                'message' => $exception->getMessage(),
            ]);

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
        $restock->load(['supplier', 'createdBy', 'confirmedBy', 'ratingGivenBy', 'items.product']);
        $statusOptions = RestockOrder::statusOptions();

        return view('admin.restocks.show', compact('restock', 'statusOptions'));
    }

    public function rate(RestockOrderRatingRequest $request, RestockOrder $restockOrder): RedirectResponse
    {
        if (! $restockOrder->canBeRated()) {
            return redirect()
                ->route('admin.restocks.show', $restockOrder)
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
            ->route('admin.restocks.show', $restockOrder)
            ->with('success', 'Supplier rating has been saved successfully.');
    }

    public function markInTransit(RestockOrder $restock): RedirectResponse
    {
        if (! $restock->canBeMarkedInTransit()) {
            return redirect()
                ->route('admin.restocks.show', $restock)
                ->withErrors([
                    'general' => 'Only confirmed orders can be marked as in transit.',
                ]);
        }

        $restock->update([
            'status' => RestockOrder::STATUS_IN_TRANSIT,
        ]);

        return redirect()
            ->route('admin.restocks.show', $restock)
            ->with('success', 'Restock order marked as in transit.');
    }

    public function markReceived(RestockOrder $restock): RedirectResponse
    {
        if (! $restock->canBeMarkedReceived()) {
            return redirect()
                ->route('admin.restocks.show', $restock)
                ->withErrors([
                    'general' => 'Only in transit orders can be marked as received.',
                ]);
        }

        $restock->update([
            'status' => RestockOrder::STATUS_RECEIVED,
        ]);

        return redirect()
            ->route('admin.restocks.show', $restock)
            ->with('success', 'Restock order marked as received.');
    }

    public function cancel(RestockOrder $restock): RedirectResponse
    {
        if (! $restock->canBeCancelled()) {
            return redirect()
                ->route('admin.restocks.show', $restock)
                ->withErrors([
                    'general' => 'Only pending or confirmed orders can be cancelled.',
                ]);
        }

        $restock->update([
            'status' => RestockOrder::STATUS_CANCELLED,
        ]);

        return redirect()
            ->route('admin.restocks.show', $restock)
            ->with('success', 'Restock order cancelled.');
    }

    public function supplierIndex(Request $request): View
    {
        $supplier = $request->user();

        $search = (string) $request->query('q', '');
        $statusFilter = (string) $request->query('status', '');

        $restockOrdersQuery = RestockOrder::query()
            ->where('supplier_id', $supplier->id)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('po_number', 'like', '%' . $search . '%');
            })
            ->when($statusFilter !== '', function ($query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        $restockOrders = $restockOrdersQuery
            ->paginate(RestockOrder::DEFAULT_PER_PAGE)
            ->withQueryString();

        $statusOptions = RestockOrder::statusOptions();

        return view('supplier.restocks.index', compact('restockOrders', 'statusOptions', 'search', 'statusFilter'));
    }

    public function supplierShow(Request $request, RestockOrder $restock): View
    {
        $this->abortIfSupplierDoesNotOwn($restock, $request->user()->id);

        $restock->load(['supplier', 'createdBy', 'confirmedBy', 'items.product']);
        $statusOptions = RestockOrder::statusOptions();

        return view('supplier.restocks.show', compact('restock', 'statusOptions'));
    }

    public function supplierConfirm(Request $request, RestockOrder $restock): RedirectResponse
    {
        $this->abortIfSupplierDoesNotOwn($restock, $request->user()->id);

        if (! $restock->canBeConfirmedBySupplier()) {
            return redirect()
                ->route('supplier.restocks.show', $restock)
                ->withErrors(['general' => 'Only pending orders can be confirmed.']);
        }

        $restock->update([
            'status' => RestockOrder::STATUS_CONFIRMED,
            'confirmed_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('supplier.restocks.show', $restock)
            ->with('success', 'Restock order confirmed.');
    }

    public function supplierReject(Request $request, RestockOrder $restock): RedirectResponse
    {
        $this->abortIfSupplierDoesNotOwn($restock, $request->user()->id);

        if (! $restock->canBeConfirmedBySupplier()) {
            return redirect()
                ->route('supplier.restocks.show', $restock)
                ->withErrors(['general' => 'Only pending orders can be rejected.']);
        }

        $rejectReason = trim((string) $request->input('reject_reason', ''));
        $updates = ['status' => RestockOrder::STATUS_CANCELLED];

        if ($rejectReason !== '') {
            $existingNotes = (string) ($restock->notes ?? '');
            $notePrefix = $existingNotes !== '' ? $existingNotes . PHP_EOL : '';
            $updates['notes'] = $notePrefix . 'Supplier rejection reason: ' . $rejectReason;
        }

        $restock->update($updates);

        return redirect()
            ->route('supplier.restocks.show', $restock)
            ->with('success', 'Restock order rejected.');
    }

    private function abortIfSupplierDoesNotOwn(RestockOrder $restock, int $supplierId): void
    {
        if ($restock->supplier_id !== $supplierId) {
            abort(403);
        }
    }
}
