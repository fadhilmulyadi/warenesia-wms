<?php

namespace App\Http\Controllers;

use App\Enums\IncomingTransactionStatus;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\IncomingTransactionRequest;
use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\IncomingTransactionService;
use App\Support\CsvExporter;
use App\Support\TransactionPrefill;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomingTransactionController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = IncomingTransaction::DEFAULT_PER_PAGE;

    private const MAX_PER_PAGE = 250;

    private const ROLE_STAFF = 'staff';

    private const EXPORT_CHUNK_SIZE = 200;

    public function __construct(private readonly IncomingTransactionService $transactionService) {}

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

        $initialItems = [];
        $restockOrder = null;

        if ($request->has('restock_order_id')) {
            $restockOrder = \App\Models\RestockOrder::with('items.product')->find($request->input('restock_order_id'));

            if ($restockOrder) {
                $prefilledSupplierId = $restockOrder->supplier_id;

                foreach ($restockOrder->items as $item) {
                    $initialItems[] = [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'original_quantity' => $item->quantity, // For discrepancy check
                    ];
                }
            }
        } elseif ($prefilledProductId) {
            $initialItems[] = [
                'product_id' => (int) $prefilledProductId,
                'quantity' => (int) $prefilledQuantity,
                'unit_cost' => (float) ($prefilledUnitCost ?? 0),
            ];
        }

        return view(
            'purchases.create',
            compact(
                'suppliers',
                'products',
                'today',
                'prefilledProductId',
                'prefilledSupplierId',
                'prefilledQuantity',
                'prefilledUnitCost',
                'initialItems',
                'restockOrder'
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
            $transaction = $this->transactionService->create($validated, $request->user());

            return redirect()
                ->route('purchases.show', $transaction)
                ->with('success', 'Transaksi masuk berhasil ditambahkan.');
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

        $transactionQuery = $this->transactionService->indexQuery([
            'search' => (string) $request->query('q', ''),
            'status' => (array) $request->query('status', []),
            'supplier_id' => $request->query('supplier_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $sort,
            'direction' => $direction,
        ], $request->user());

        $fileName = 'purchases-'.now()->format('Ymd-His').'.csv';

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
                            $this->incomingStatusLabel($transaction->status),
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
            $this->transactionService->update($purchase, $validated, $request->user());

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
                ->withErrors(['general' => 'Gagal memperbarui transaksi: '.$exception->getMessage()]);
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
            $this->transactionService->verify($purchase, $request->user());

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Transaksi masuk berhasil disetujui.');
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
            $this->transactionService->complete($purchase, $request->user());

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Transaksi masuk berhasil diproses.');
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
            $this->transactionService->reject($purchase, $request->user(), $request->input('reason'));

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Transaksi masuk berhasil ditolak.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->withErrors(['general' => $exception->getMessage()]);
        }
    }

    private function incomingStatusOptions(): array
    {
        return collect(IncomingTransactionStatus::cases())
            ->mapWithKeys(fn (IncomingTransactionStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    private function incomingStatusLabel(IncomingTransactionStatus|string $status): string
    {
        $statusEnum = $status instanceof IncomingTransactionStatus
            ? $status
            : IncomingTransactionStatus::tryFrom((string) $status);

        return $statusEnum?->label() ?? ucfirst((string) $status);
    }
}
