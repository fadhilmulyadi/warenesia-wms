<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\OutgoingTransactionRequest;
use App\Exceptions\InsufficientStockException;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\User;
use App\Support\CsvExporter;
use App\Support\TransactionPrefill;
use App\Services\TransactionService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OutgoingTransactionController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = OutgoingTransaction::DEFAULT_PER_PAGE;
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
        return redirect()->route('transactions.index', ['tab' => 'outgoing']);
    }

    /**
     * Show the form for creating a new resource.
     */
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

        return view(
            'sales.create',
            compact(
                'products', 
                'today', 
                'prefilledProductId',
                'prefilledCustomerName',
                'prefilledQuantity',
                'prefilledUnitPrice'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OutgoingTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', OutgoingTransaction::class);

        $validated = $request->validated();

        try {
            $transaction = $this->transactionService->createOutgoing($validated, $request->user());

            return redirect()
                ->route('sales.show', $transaction)
                ->with('success', 'Outgoing transaction created successfully. Pending approval.');
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

        $transactionQuery = $this->buildOutgoingTransactionIndexQuery($request, $sort, $direction);
        $this->applyStaffScope($transactionQuery, $request->user());

        $fileName = 'sales-' . now()->format('Ymd-His') . '.csv';

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
                            $this->outgoingStatusLabel((string) $transaction->status),
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

    /**
     * Display the specified resource.
     */
    public function show(OutgoingTransaction $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['createdBy', 'approvedBy', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OutgoingTransaction $sale): View|RedirectResponse
    {
        $this->authorize('update', $sale);

        // Hanya transaksi berstatus 'pending' yang boleh diedit
        if (! $sale->isPending()) {
            return redirect()
                ->route('sales.show', $sale)
                ->withErrors(['general' => 'Only pending transactions can be edited.']);
        }

        // Eager load items untuk memastikan data produk tersedia di view
        $sale->load(['items']); 
        $products = Product::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OutgoingTransactionRequest $request, OutgoingTransaction $sale): RedirectResponse
    {
        $this->authorize('update', $sale);

        if (! $sale->isPending()) {
            return back()->withErrors(['general' => 'Hanya transaksi status Pending yang dapat diedit.']);
        }

        $validated = $request->validated();

        try {
            $this->transactionService->updateOutgoing($sale, $validated, $request->user());

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Transaksi barang keluar berhasil diperbarui.');

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
    public function destroy(OutgoingTransaction $sale)
    {
        $this->authorize('delete', $sale);

        if (! $sale->isPending()) {
            return back()->withErrors(['general' => 'Hanya transaksi status Pending yang dapat dihapus.']);
        }

        $sale->delete();

        return redirect()
            ->route('transactions.index', ['tab' => 'outgoing'])
            ->with('success', 'Transaksi barang keluar berhasil dihapus.');
    }

    public function approve(Request $request, OutgoingTransaction $sale): RedirectResponse
    {
        $this->authorize('approve', $sale);

        try {
            $this->transactionService->approveOutgoing($sale, $request->user());

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Transaction approved and stock updated.');
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
            $this->transactionService->shipOutgoing($sale, $request->user());

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Transaction marked as shipped.');
        } catch (DomainException $exception) {
            return redirect()
                ->route('sales.show', $sale)
                ->withErrors(['general' => $exception->getMessage()]);
        }
    }

    private function buildOutgoingTransactionIndexQuery(
        Request $request,
        string $sort,
        string $direction
    ): Builder {
        $search = (string) $request->query('q', '');

        $query = OutgoingTransaction::query()
            ->with(['createdBy', 'approvedBy']);

        $this->applySearch($query, $search, ['transaction_number', 'customer_name']);

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

    private function outgoingStatusOptions(): array
    {
        return [
            OutgoingTransaction::STATUS_PENDING => 'Pending',
            OutgoingTransaction::STATUS_APPROVED => 'Approved',
            OutgoingTransaction::STATUS_SHIPPED => 'Shipped',
        ];
    }

    private function outgoingStatusLabel(string $status): string
    {
        $options = $this->outgoingStatusOptions();

        return $options[$status] ?? ucfirst($status);
    }
}
