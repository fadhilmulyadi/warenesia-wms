<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncomingTransactionRequest;
use App\Models\IncomingTransaction;
use App\Models\IncomingTransactionItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncomingTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
$       $search = $request->query('q', '');
        $statusFilter = $request->query('status', '');

        $transactionsQuery = IncomingTransaction::query()
            ->with(['supplier', 'createdBy'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('transaction_number', 'like', '%' . $search . '%')
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search): void {
                            $supplierQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($statusFilter !== '', function ($query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        $transactions = $transactionsQuery
            ->paginate(IncomingTransaction::DEFAULT_PER_PAGE)
            ->withQueryString();

        $statusOptions = [
            IncomingTransaction::STATUS_PENDING => 'Pending',
            IncomingTransaction::STATUS_VERIFIED => 'Verified',
            IncomingTransaction::STATUS_COMPLETED => 'Completed',
            IncomingTransaction::STATUS_REJECTED => 'Rejected',
        ];

        return view('admin.purchases.index', compact('transactions', 'search', 'statusFilter', 'statusOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $today = now()->toDateString();

        return view('admin.purchases.create', compact('suppliers', 'products', 'today'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomingTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $itemsData = $validated['items'];

        if (count($itemsData) === 0) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'At least one product must be added to the transaction.']);
        }

        $transactionNumber = IncomingTransaction::generateNextTransactionNumber();

        DB::beginTransaction();

        try {
            $totalItems = count($itemsData);
            $totalQuantity = 0;
            $totalAmount = 0.0;

            $transaction = IncomingTransaction::create([
                'transaction_number' => $transactionNumber,
                'transaction_date' => $validated['transaction_date'],
                'supplier_id' => $validated['supplier_id'],
                'created_by' => $request->user()->id,
                'verified_by' => null,
                'status' => IncomingTransaction::STATUS_PENDING,
                'total_items' => 0,
                'total_quantity' => 0,
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $quantity = (int) $itemData['quantity'];
                $unitCost = isset($itemData['unit_cost']) ? (float) $itemData['unit_cost'] : 0.0;
                $lineTotal = $quantity * $unitCost;

                $totalQuantity += $quantity;
                $totalAmount += $lineTotal;

                IncomingTransactionItem::create([
                    'incoming_transaction_id' => $transaction->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
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
                ->route('admin.purchases.show', $transaction)
                ->with('success', 'Incoming transaction created successfully. Pending verification.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create incoming transaction. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingTransaction $purchase): View
    {
        $purchase->load(['supplier', 'createdBy', 'verifiedBy', 'items.product']);

        return view('admin.purchases.show', compact('purchase'));
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

    public function verify(Request $request, IncomingTransaction $purchase): RedirectResponse
    {
        if (! $purchase->canBeVerified()) {
            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->withErrors(['general' => 'Only pending transactions can be verified.']);
        }

        DB::beginTransaction();

        try {
            $purchase->loadMissing('items.product');

            foreach ($purchase->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    continue;
                }

                $product->increaseStock((int) $item->quantity);
            }

            $purchase->update([
                'status' => IncomingTransaction::STATUS_VERIFIED,
                'verified_by' => $request->user()->id,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with('success', 'Transaction verified and stock updated.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->withErrors(['general' => 'Failed to verify transaction. Please try again.']);
        }
    }

    public function complete(IncomingTransaction $purchase): RedirectResponse
    {
        if (! $purchase->canBeCompleted()) {
            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->withErrors(['general' => 'Only verified transactions can be marked as completed.']);
        }

        $purchase->update([
            'status' => IncomingTransaction::STATUS_COMPLETED,
        ]);

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('success', 'Transaction marked as completed.');
    }
}