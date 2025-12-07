<?php

namespace App\Http\Controllers;

use App\Enums\IncomingTransactionStatus;
use App\Enums\OutgoingTransactionStatus;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Supplier;
use App\Services\IncomingTransactionService;
use App\Services\OutgoingTransactionService;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = 10;

    private const MAX_PER_PAGE = 250;

    private const TYPE_INCOMING = 'barang_masuk';

    private const TYPE_OUTGOING = 'barang_keluar';

    public function __construct(
        private readonly IncomingTransactionService $incomingTransactions,
        private readonly OutgoingTransactionService $outgoingTransactions
    ) {
    }

    public function index(Request $request): View
    {
        $activeTab = $this->resolveTransactionMode($request);
        $policyTarget = $activeTab === 'incoming'
            ? IncomingTransaction::class
            : OutgoingTransaction::class;

        $this->authorize('viewAny', $policyTarget);

        $typeParam = $activeTab === 'incoming' ? self::TYPE_INCOMING : self::TYPE_OUTGOING;
        $tabs = $this->transactionTabs();

        $perPage = $this->resolvePerPage(
            $request,
            self::DEFAULT_PER_PAGE,
            self::MAX_PER_PAGE
        );

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['transaction_number', 'transaction_date'],
            defaultSort: 'transaction_date',
            defaultDirection: 'desc'
        );

        $filters = [
            'search' => (string) $request->query('q', ''),
            'status' => (array) $request->query('status', []),
            'supplier_id' => $request->query('supplier_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $sort,
            'direction' => $direction,
        ];

        $transactionsQuery = $activeTab === 'incoming'
            ? $this->incomingTransactions->indexQuery($filters, $request->user())
            : $this->outgoingTransactions->indexQuery($filters, $request->user());

        $transactions = $transactionsQuery
            ->paginate($perPage)
            ->withQueryString();

        $search = (string) $request->query('q', '');
        $statusOptions = $activeTab === 'incoming'
            ? $this->incomingStatusOptions()
            : $this->outgoingStatusOptions();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('transactions.index', compact(
            'tabs',
            'activeTab',
            'transactions',
            'search',
            'statusOptions',
            'suppliers',
            'typeParam',
            'sort',
            'direction',
            'perPage'
        ));
    }

    private function resolveTransactionMode(Request $request): string
    {
        $routeType = (string) ($request->route('type') ?? $request->route('mode') ?? '');
        $tab = (string) $request->query('tab', '');
        $type = (string) $request->query('type', '');

        $candidate = $type !== '' ? $type : $tab;
        if ($routeType !== '') {
            $candidate = $routeType;
        }

        if (in_array($candidate, ['outgoing', self::TYPE_OUTGOING], true)) {
            return 'outgoing';
        }

        if (in_array($candidate, ['incoming', self::TYPE_INCOMING], true)) {
            return 'incoming';
        }

        return 'incoming';
    }

    private function transactionTabs(): array
    {
        return [
            'incoming' => [
                'label' => 'Barang Masuk',
                'query' => ['type' => self::TYPE_INCOMING],
            ],
            'outgoing' => [
                'label' => 'Barang Keluar',
                'query' => ['type' => self::TYPE_OUTGOING],
            ],
        ];
    }

    private function incomingStatusOptions(): array
    {
        return collect(IncomingTransactionStatus::cases())
            ->mapWithKeys(fn(IncomingTransactionStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    private function outgoingStatusOptions(): array
    {
        return collect(OutgoingTransactionStatus::cases())
            ->mapWithKeys(fn(OutgoingTransactionStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $activeTab = $this->resolveTransactionMode($request);

        $policyTarget = $activeTab === 'incoming'
            ? IncomingTransaction::class
            : OutgoingTransaction::class;

        $this->authorize('viewAny', $policyTarget);

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['transaction_number', 'transaction_date'],
            defaultSort: 'transaction_date',
            defaultDirection: 'desc'
        );

        $filters = [
            'search' => (string) $request->query('q', ''),
            'status' => (array) $request->query('status', []),
            'supplier_id' => $request->query('supplier_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $sort,
            'direction' => $direction,
        ];

        $query = $activeTab === 'incoming'
            ? $this->incomingTransactions->indexQuery($filters, $request->user())
            : $this->outgoingTransactions->indexQuery($filters, $request->user());

        $fileName = 'transactions_' . $activeTab . '_' . date('Y-m-d_His') . '.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($query, $activeTab) {
            if ($activeTab === 'incoming') {
                $output->fputcsv([
                    'Transaction Number',
                    'Date',
                    'Supplier',
                    'Created By',
                    'Verified By',
                    'Status',
                    'Total Items',
                    'Total Quantity',
                    'Total Amount',
                    'Notes',
                ]);

                $query->chunk(100, function ($transactions) use ($output) {
                    foreach ($transactions as $transaction) {
                        $output->fputcsv([
                            $transaction->transaction_number,
                            $transaction->transaction_date->format('Y-m-d'),
                            $transaction->supplier->name ?? '-',
                            $transaction->createdBy->name ?? '-',
                            $transaction->verifiedBy->name ?? '-',
                            $transaction->status->label(),
                            $transaction->total_items,
                            $transaction->total_quantity,
                            number_format($transaction->total_amount, 2),
                            $transaction->notes,
                        ]);
                    }
                });
            } else {
                $output->fputcsv([
                    'Transaction Number',
                    'Date',
                    'Customer Name',
                    'Created By',
                    'Approved By',
                    'Status',
                    'Total Items',
                    'Total Quantity',
                    'Total Amount',
                    'Notes',
                ]);

                $query->chunk(100, function ($transactions) use ($output) {
                    foreach ($transactions as $transaction) {
                        $output->fputcsv([
                            $transaction->transaction_number,
                            $transaction->transaction_date->format('Y-m-d'),
                            $transaction->customer_name,
                            $transaction->createdBy->name ?? '-',
                            $transaction->approvedBy->name ?? '-',
                            $transaction->status->label(),
                            $transaction->total_items,
                            $transaction->total_quantity,
                            number_format($transaction->total_amount, 2),
                            $transaction->notes,
                        ]);
                    }
                });
            }
        });
    }
}
