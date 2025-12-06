<?php

namespace App\Http\Controllers;

use App\Enums\IncomingTransactionStatus;
use App\Enums\OutgoingTransactionStatus;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Supplier;
use App\Services\IncomingTransactionService;
use App\Services\OutgoingTransactionService;
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
    ) {}

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
            'customer_ids' => (array) $request->query('customer_id', []),
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
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return view('transactions.index', compact(
            'tabs',
            'activeTab',
            'transactions',
            'search',
            'statusOptions',
            'suppliers',
            'customers',
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
            ->mapWithKeys(fn (IncomingTransactionStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    private function outgoingStatusOptions(): array
    {
        return collect(OutgoingTransactionStatus::cases())
            ->mapWithKeys(fn (OutgoingTransactionStatus $status) => [$status->value => $status->label()])
            ->all();
    }
}
