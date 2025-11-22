<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestockOrderRequest;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\RestockOrderItem;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RestockOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $supplierFilter = (int) $request->query('supplier_id', 0);
        $statusFilter = (string) $request->query('status', '');

        $restockOrdersQuery = RestockOrder::query()
            ->with(['supplier', 'createdBy'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('po_number', 'like', '%' . $search . '%');
            })
            ->when($supplierFilter > 0, function ($query) use ($supplierFilter): void {
                $query->where('supplier_id', $supplierFilter);
            })
            ->when($statusFilter !== '', function ($query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        $restockOrders = $restockOrdersQuery
            ->paginate(RestockOrder::DEFAULT_PER_PAGE)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        $statusOptions = [
            RestockOrder::STATUS_PENDING => 'Pending',
            RestockOrder::STATUS_CONFIRMED => 'Confirmed',
            RestockOrder::STATUS_IN_TRANSIT => 'In transit',
            RestockOrder::STATUS_RECEIVED => 'Received',
            RestockOrder::STATUS_CANCELLED => 'Cancelled',
        ];

        return view(
            'admin.restocks.index',
            compact(
                'restockOrders',
                'suppliers',
                'statusOptions',
                'search',
                'supplierFilter',
                'statusFilter'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
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
    public function show(RestockOrder $restockOrder): View
    {
        $restockOrder->load(['supplier', 'createdBy', 'confirmedBy', 'items.product']);

        return view('admin.restocks.show', compact('restockOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
