<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\IncomingTransactionRequest;
use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Support\CsvExporter;
use App\Support\TransactionPrefill;
use App\Services\TransactionService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use InvalidArgumentException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomingTransactionController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = IncomingTransaction::DEFAULT_PER_PAGE;
    private const MAX_PER_PAGE = 250;
    private const ROLE_STAFF = 'staff';
    private const EXPORT_CHUNK_SIZE = 200;

    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('transactions.index', ['tab' => 'incoming']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', IncomingTransaction::class);

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $today = now()->toDateString();

        $prefill = TransactionPrefill::forPurchases($request);
        $prefilledProductId = $prefill['product_id'];
        $prefilledSupplierId = $prefill['supplier_id'];
        $prefilledQuantity = $prefill['quantity'];
        $prefilledUnitCost = $prefill['unit_cost'];

        return view(
            'purchases.create',
            compact(
                'suppliers', 
                'products', 
                'today', 
                'prefilledProductId',
                'prefilledSupplierId',
                'prefilledQuantity',
                'prefilledUnitCost'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomingTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', IncomingTransaction::class);

        $validated = $request->validated();

        try {
            $transaction = $this->transactionService->createIncoming($validated, $request->user());

            return redirect()
                ->route('purchases.show', $transaction)
                ->with('success', 'Incoming transaction created successfully. Pending verification.');
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['items' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create incoming transaction. Please try again.']);
        }
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', IncomingTransaction::class);

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['transaction_date', 'transaction_number', 'status', 'total_items', 'total_quantity', 'total_amount', 'created_at'],
            defaultSort: 'transaction_date',
            defaultDirection: 'desc'
        );

        $transactionQuery = $this->buildIncomingTransactionIndexQuery($request, $sort, $direction);
        $this->applyStaffScope($transactionQuery, $request->user());

        $fileName = 'purchases-' . now()->format('Ymd-His') . '.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($transactionQuery): void {
            $output->fputcsv([
                'Transaction Number',
                'Transaction Date',
                'Supplier Name',
                'Status',
                'Total Items',
                'Total Quantity',
                'Total Amount',
                'Created By',
                'Verified By',
                'Notes',
            ]);

            $transactionQuery
                ->chunk(self::EXPORT_CHUNK_SIZE, function (Collection $transactions) use ($output): void {
                    foreach ($transactions as $transaction) {
                        $output->fputcsv([
                            $transaction->transaction_number,
                            optional($transaction->transaction_date)->format('Y-m-d'),
                            optional($transaction->supplier)->name ?? '',
                            $this->incomingStatusLabel((string) $transaction->status),
                            (int) $transaction->total_items,
                            (int) $transaction->total_quantity,
                            (float) $transaction->total_amount,
                            optional($transaction->createdBy)->name ?? '',
                            optional($transaction->verifiedBy)->name ?? '',
                            (string) $transaction->notes,
                        ]);
                    }
                });
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingTransaction $purchase): View
    {
        $this->authorize('view', $purchase);

        $purchase->load(['supplier', 'createdBy', 'verifiedBy', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IncomingTransaction $purchase): View|RedirectResponse
    {
        $this->authorize('update', $purchase);

        if (! $purchase->isPending()) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->withErrors(['general' => 'Only pending transactions can be edited.']);
        }

        $purchase->load(['items']);
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncomingTransactionRequest $request, IncomingTransaction $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        if (! $purchase->isPending()) {
            return back()->withErrors(['general' => 'Only pending transactions can be edited.']);
        }

        $validated = $request->validated();

        try {
            $this->transactionService->updateIncoming($purchase, $validated, $request->user());

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Transaksi berhasil diperbarui.');

        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['items' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Gagal memperbarui transaksi: ' . $exception->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomingTransaction $purchase)
    {
        $this->authorize('delete', $purchase);

        $purchase->delete();

        return redirect()
            ->route('transactions.index', ['tab' => 'incoming'])
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function verify(Request $request, IncomingTransaction $purchase): RedirectResponse
    {
        $this->authorize('verify', $purchase);

        try {
            $this->transactionService->approveIncoming($purchase, $request->user());

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Transaction verified and stock updated.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->withErrors(['general' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->withErrors(['general' => 'Failed to verify transaction. Please try again.']);
        }
    }

    public function complete(Request $request, IncomingTransaction $purchase): RedirectResponse
    {
        $this->authorize('complete', $purchase);

        try {
            $this->transactionService->completeIncoming($purchase, $request->user());

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Transaction marked as completed.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->withErrors(['general' => $exception->getMessage()]);
        }
    }

    public function reject(Request $request, IncomingTransaction $purchase): RedirectResponse
    {
        $this->authorize('reject', $purchase);

        try {
            $this->transactionService->rejectIncoming($purchase, $request->user(), $request->input('reason'));

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Transaction rejected.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->withErrors(['general' => $exception->getMessage()]);
        }
    }

    private function buildIncomingTransactionIndexQuery(
        Request $request,
        string $sort,
        string $direction
    ): Builder {
        $search = (string) $request->query('q', '');

        $query = IncomingTransaction::query()
            ->with(['supplier', 'createdBy', 'verifiedBy']);

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search): void {
                $this->applySearch($searchQuery, $search, ['transaction_number']);
                $searchQuery->orWhereHas('supplier', function (Builder $supplierQuery) use ($search): void {
                    $supplierQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $this->applyFilters($query, $request, [
            'status' => 'status',
        ]);

        $this->applyDateRange($query, $request, 'transaction_date');

        $query->orderBy($sort, $direction)
            ->orderBy('id');

        return $query;
    }

    private function applyStaffScope(Builder $query, ?User $user): void
    {
        if ($user !== null && $user->role === self::ROLE_STAFF) {
            $query->where('created_by', $user->id);
        }
    }

    private function incomingStatusOptions(): array
    {
        return [
            IncomingTransaction::STATUS_PENDING => 'Pending',
            IncomingTransaction::STATUS_VERIFIED => 'Verified',
            IncomingTransaction::STATUS_COMPLETED => 'Completed',
            // IncomingTransaction::STATUS_REJECTED => 'Rejected',
        ];
    }

    private function incomingStatusLabel(string $status): string
    {
        $options = $this->incomingStatusOptions();

        return $options[$status] ?? ucfirst($status);
    }
}
