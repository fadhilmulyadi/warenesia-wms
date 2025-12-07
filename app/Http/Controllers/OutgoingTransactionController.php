<?php

namespace App\Http\Controllers;

use App\Enums\OutgoingTransactionStatus;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\OutgoingTransactionRequest;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Services\OutgoingTransactionService;
use App\Support\CsvExporter;
use App\Support\TransactionPrefill;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OutgoingTransactionController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = OutgoingTransaction::DEFAULT_PER_PAGE;

    private const MAX_PER_PAGE = 250;

    private const ROLE_STAFF = 'staff';

    private const EXPORT_CHUNK_SIZE = 200;

    public function __construct(private readonly OutgoingTransactionService $transactionService) {}

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('transactions.index', ['tab' => 'outgoing']);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', OutgoingTransaction::class);

        $products = Product::orderBy('name')->get();
        $today = now()->toDateString();

        $prefill = TransactionPrefill::forSales($request);
        $prefilledProductId = $prefill['product_id'];
        $prefilledCustomerName = $prefill['customer_name'];
        $prefilledQuantity = $prefill['quantity'];
        $prefilledUnitPrice = $prefill['unit_price'];

        $initialItems = [];
        if ($prefilledProductId) {
            $initialItems[] = [
                'product_id' => (int) $prefilledProductId,
                'quantity' => (int) $prefilledQuantity,
                'unit_price' => (float) ($prefilledUnitPrice ?? 0),
            ];
        }

        return view(
            'sales.create',
            compact(
                'products',
                'today',
                'prefilledProductId',
                'prefilledCustomerName',
                'prefilledQuantity',
                'prefilledUnitPrice',
                'initialItems'
            )
        );
    }

    public function store(OutgoingTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', OutgoingTransaction::class);

        $validated = $request->validated();

        try {
            $transaction = $this->transactionService->create($validated, $request->user());

            return redirect()
                ->route('sales.show', $transaction)
                ->with('success', 'Transaksi keluar berhasil ditambahkan.');
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['items' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create outgoing transaction. Please try again.']);
        }
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', OutgoingTransaction::class);

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['transaction_date', 'transaction_number', 'customer_name', 'status', 'total_items', 'total_quantity', 'total_amount', 'created_at'],
            defaultSort: 'transaction_date',
            defaultDirection: 'desc'
        );

        $transactionQuery = $this->transactionService->indexQuery([
            'search' => (string) $request->query('q', ''),
            'status' => (array) $request->query('status', []),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $sort,
            'direction' => $direction,
        ], $request->user());

        $fileName = 'sales-'.now()->format('Ymd-His').'.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($transactionQuery): void {
            $output->fputcsv([
                'Transaction Number',
                'Transaction Date',
                'Customer',
                'Status',
                'Total Items',
                'Total Quantity',
                'Total Amount',
                'Created By',
                'Approved By',
                'Notes',
            ]);

            $transactionQuery
                ->chunk(self::EXPORT_CHUNK_SIZE, function (Collection $transactions) use ($output): void {
                    foreach ($transactions as $transaction) {
                        $output->fputcsv([
                            $transaction->transaction_number,
                            optional($transaction->transaction_date)->format('Y-m-d'),
                            $transaction->customer_name,
                            $this->outgoingStatusLabel($transaction->status),
                            (int) $transaction->total_items,
                            (int) $transaction->total_quantity,
                            (float) $transaction->total_amount,
                            optional($transaction->createdBy)->name ?? '',
                            optional($transaction->approvedBy)->name ?? '',
                            (string) $transaction->notes,
                        ]);
                    }
                });
        });
    }

    public function show(OutgoingTransaction $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['createdBy', 'approvedBy', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function edit(OutgoingTransaction $sale): View|RedirectResponse
    {
        $this->authorize('update', $sale);

        if (! $sale->isPending()) {
            return redirect()
                ->route('sales.show', $sale)
                ->withErrors(['general' => 'Only pending transactions can be edited.']);
        }

        $sale->load(['items']);
        $products = Product::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'products'));
    }

    public function update(OutgoingTransactionRequest $request, OutgoingTransaction $sale): RedirectResponse
    {
        $this->authorize('update', $sale);

        if (! $sale->isPending()) {
            return back()->withErrors(['general' => 'Hanya transaksi status Pending yang dapat diedit.']);
        }

        $validated = $request->validated();

        try {
            $this->transactionService->update($sale, $validated, $request->user());

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Transaksi keluar berhasil diperbarui.');

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

    public function destroy(OutgoingTransaction $sale)
    {
        $this->authorize('delete', $sale);

        if (! $sale->isPending()) {
            return back()->withErrors(['general' => 'Hanya transaksi status Pending yang dapat dihapus.']);
        }

        $sale->delete();

        return redirect()
            ->route('transactions.index', ['tab' => 'outgoing'])
            ->with('success', 'Transaksi keluar berhasil dihapus.');
    }

    public function approve(Request $request, OutgoingTransaction $sale): RedirectResponse
    {
        $this->authorize('approve', $sale);

        try {
            $this->transactionService->approve($sale, $request->user());

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Transaksi keluar berhasil disetujui.');
        } catch (InsufficientStockException|DomainException|ModelNotFoundException $exception) {
            return redirect()
                ->route('sales.show', $sale)
                ->withErrors(['general' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('sales.show', $sale)
                ->withErrors(['general' => 'Failed to approve transaction. Please try again.']);
        }
    }

    public function ship(Request $request, OutgoingTransaction $sale): RedirectResponse
    {
        $this->authorize('ship', $sale);

        try {
            $this->transactionService->ship($sale, $request->user());

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Transaksi keluar berhasil diproses.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('sales.show', $sale)
                ->withErrors(['general' => $exception->getMessage()]);
        }
    }

    private function outgoingStatusLabel(OutgoingTransactionStatus|string $status): string
    {
        $statusEnum = $status instanceof OutgoingTransactionStatus
            ? $status
            : OutgoingTransactionStatus::tryFrom((string) $status);

        return $statusEnum?->label() ?? ucfirst((string) $status);
    }
}
