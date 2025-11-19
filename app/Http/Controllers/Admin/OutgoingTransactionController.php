<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutgoingTransactionRequest;
use App\Models\OutgoingTransaction;
use App\Models\OutgoingTransactionItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutgoingTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q', '');
        $statusFilter = $request->query('status', '');

        $transactionsQuery = OutgoingTransaction::query()
            ->with(['createdBy'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('transaction_number', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%');
                });
            })
            ->when($statusFilter !== '', function ($query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        $transactions = $transactionsQuery
            ->paginate(OutgoingTransaction::DEFAULT_PER_PAGE)
            ->withQueryString();

        $statusOptions = [
            OutgoingTransaction::STATUS_PENDING => 'Pending',
            OutgoingTransaction::STATUS_APPROVED => 'Approved',
            OutgoingTransaction::STATUS_SHIPPED => 'Shipped',
        ];

        return view('admin.sales.index', compact('transactions', 'search', 'statusFilter', 'statusOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        $today = now()->toDateString();

        return view('admin.sales.create', compact('products', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OutgoingTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $itemsData = $validated['items'] ?? [];

        if (count($itemsData) === 0) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'At least one product must be added to the transaction.']);
        }

        $transactionNumber = OutgoingTransaction::generateNextTransactionNumber();

        DB::beginTransaction();

        try {
            $totalItems = count($itemsData);
            $totalQuantity = 0;
            $totalAmount = 0.0;

            $transaction = OutgoingTransaction::create([
                'transaction_number' => $transactionNumber,
                'transaction_date' => $validated['transaction_date'],
                'customer_name' => $validated['customer_name'],
                'created_by' => $request->user()->id,
                'approved_by' => null,
                'status' => OutgoingTransaction::STATUS_PENDING,
                'total_items' => 0,
                'total_quantity' => 0,
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $quantity = (int) $itemData['quantity'];
                $unitPrice = isset($itemData['unit_price']) ? (float) $itemData['unit_price'] : 0.0;
                $lineTotal = $quantity * $unitPrice;

                $totalQuantity += $quantity;
                $totalAmount += $lineTotal;

                OutgoingTransactionItem::create([
                    'outgoing_transaction_id' => $transaction->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $transaction->update([
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.sales.show', $transaction)
                ->with('success', 'Outgoing transaction created successfully. Pending approval.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create outgoing transaction. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OutgoingTransaction $sale): View
    {
        $sale->load(['createdBy', 'approvedBy', 'items.product']);

        return view('admin.sales.show', compact('sale'));
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

    public function approve(Request $request, OutgoingTransaction $sale): RedirectResponse
    {
        if (! $sale->canBeApproved()) {
            return redirect()
                ->route('admin.sales.show', $sale)
                ->withErrors(['general' => 'Only pending transactions can be approved.']);
        }

        DB::beginTransaction();

        try {
            $sale->loadMissing('items.product');

            foreach ($sale->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    DB::rollBack();

                    return redirect()
                        ->route('admin.sales.show', $sale)
                        ->withErrors(['general' => 'One or more products in this transaction no longer exist.']);
                }

                if (! $product->hasSufficientStock((int) $item->quantity)) {
                    DB::rollBack();

                    return redirect()
                        ->route('admin.sales.show', $sale)
                        ->withErrors([
                            'general' => 'Insufficient stock for product: ' . $product->name,
                        ]);
                }
            }

            foreach ($sale->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    continue;
                }

                $product->decreaseStock((int) $item->quantity);
            }

            $sale->update([
                'status' => OutgoingTransaction::STATUS_APPROVED,
                'approved_by' => $request->user()->id,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.sales.show', $sale)
                ->with('success', 'Transaction approved and stock updated.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->route('admin.sales.show', $sale)
                ->withErrors(['general' => 'Failed to approve transaction. Please try again.']);
        }
    }

    public function ship(OutgoingTransaction $sale): RedirectResponse
    {
        if (! $sale->canBeShipped()) {
            return redirect()
                ->route('admin.sales.show', $sale)
                ->withErrors(['general' => 'Only approved transactions can be marked as shipped.']);
        }

        $sale->update([
            'status' => OutgoingTransaction::STATUS_SHIPPED,
        ]);

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('success', 'Transaction marked as shipped.');
    }
}