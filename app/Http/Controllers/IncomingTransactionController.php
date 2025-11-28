<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncomingTransactionRequest;
use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\CsvExporter;
use App\Services\TransactionService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomingTransactionController extends Controller
{
    private const ROLE_STAFF = 'staff';
    private const EXPORT_CHUNK_SIZE = 200;

    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', IncomingTransaction::class);

        $transactionsQuery = $this->buildIncomingTransactionIndexQuery($request);

        $transactions = $transactionsQuery
            ->paginate(IncomingTransaction::DEFAULT_PER_PAGE)
            ->withQueryString();

        $search = (string) $request->query('q', '');
        $statusFilter = (string) $request->query('status', '');
        $statusOptions = $this->incomingStatusOptions();

        return view('purchases.index', compact('transactions', 'search', 'statusFilter', 'statusOptions'));
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

        $prefilledProductId = $this->resolvePrefilledProductId($request);

        return view(
            'purchases.create',
            compact('suppliers', 'products', 'today', 'prefilledProductId')
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

        $transactionQuery = $this->buildIncomingTransactionIndexQuery($request);
        $user = $request->user();

        if ($user !== null && $user->role === self::ROLE_STAFF) {
            $transactionQuery->where('created_by', $user->id);
        }

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
                ->orderBy('transaction_date')
                ->orderBy('id')
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
    public function edit(IncomingTransaction $purchase): View
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
        $this->authorize('verify', $purchase);

        try {
            $this->transactionService->verifyIncoming($purchase, $request->user());

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

    private function resolvePrefilledProductId(Request $request): ?int
    {
        $productId = $request->query('product_id');

        if ($productId === null || $productId === '') {
            return null;
        }

        return is_numeric($productId) ? (int) $productId : null;
    }

    private function buildIncomingTransactionIndexQuery(Request $request): Builder
    {
        $search = (string) $request->query('q', '');
        $statusFilter = (string) $request->query('status', '');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        return IncomingTransaction::query()
            ->with(['supplier', 'createdBy', 'verifiedBy'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('transaction_number', 'like', '%' . $search . '%')
                        ->orWhereHas('supplier', function (Builder $supplierQuery) use ($search): void {
                            $supplierQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($statusFilter !== '', function (Builder $query) use ($statusFilter): void {
                $query->where('status', $statusFilter);
            })
            ->when($dateFrom, function (Builder $query) use ($dateFrom): void {
                $query->whereDate('transaction_date', '>=', $dateFrom);
            })
            ->when($dateTo, function (Builder $query) use ($dateTo): void {
                $query->whereDate('transaction_date', '<=', $dateTo);
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');
    }

    private function incomingStatusOptions(): array
    {
        return [
            IncomingTransaction::STATUS_PENDING => 'Pending',
            IncomingTransaction::STATUS_VERIFIED => 'Verified',
            IncomingTransaction::STATUS_COMPLETED => 'Completed',
            IncomingTransaction::STATUS_REJECTED => 'Rejected',
        ];
    }

    private function incomingStatusLabel(string $status): string
    {
        $options = $this->incomingStatusOptions();

        return $options[$status] ?? ucfirst($status);
    }
}
