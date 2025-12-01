<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 250;
    private const TYPE_INCOMING = 'barang_masuk';
    private const TYPE_OUTGOING = 'barang_keluar';

    /**
     * Display a listing of the resource.
     */
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

        $transactionsQuery = $activeTab === 'incoming'
            ? $this->buildIncomingIndexQuery($request, $sort, $direction)
            : $this->buildOutgoingIndexQuery($request, $sort, $direction);

        $this->applyStaffScope($transactionsQuery, $request);

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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

    private function buildIncomingIndexQuery(
        Request $request,
        string $sort,
        string $direction
    ): Builder {
        $search = (string) $request->query('q', '');

        $query = IncomingTransaction::query()
            ->with(['supplier', 'createdBy']);

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search): void {
                $this->applySearch($searchQuery, $search, ['transaction_number']);
                $searchQuery->orWhereHas('supplier', function (Builder $supplierQuery) use ($search): void {
                    $supplierQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $this->applyFilters($query, $request, [
            'status' => function (Builder $statusQuery, $value): void {
                $statuses = array_values(array_intersect(
                    (array) $value,
                    array_keys($this->incomingStatusOptions())
                ));

                if (count($statuses) > 0) {
                    $statusQuery->whereIn('status', $statuses);
                }
            },
            'supplier_id' => 'supplier_id',
        ]);

        $this->applyDateRange($query, $request, 'transaction_date');

        $query->orderBy($sort, $direction)
            ->orderBy('id');

        return $query;
    }

    private function buildOutgoingIndexQuery(
        Request $request,
        string $sort,
        string $direction
    ): Builder {
        $search = (string) $request->query('q', '');

        $query = OutgoingTransaction::query()
            ->with(['createdBy']);

        $this->applySearch($query, $search, ['transaction_number', 'customer_name']);

        $this->applyFilters($query, $request, [
            'status' => function (Builder $statusQuery, $value): void {
                $statuses = array_values(array_intersect(
                    (array) $value,
                    array_keys($this->outgoingStatusOptions())
                ));

                if (count($statuses) > 0) {
                    $statusQuery->whereIn('status', $statuses);
                }
            },
            'customer_id' => function (Builder $customerQuery, $value): void {
                $customerIds = array_values(array_filter((array) $value, static function ($id) {
                    return $id !== null && $id !== '';
                }));

                if (count($customerIds) === 0) {
                    return;
                }

                $customerNames = Customer::whereIn('id', $customerIds)
                    ->pluck('name')
                    ->filter()
                    ->values();

                if ($customerNames->isNotEmpty()) {
                    $customerQuery->whereIn('customer_name', $customerNames);
                }
            },
        ]);

        $this->applyDateRange($query, $request, 'transaction_date');

        $query->orderBy($sort, $direction)
            ->orderBy('id');

        return $query;
    }

    private function applyStaffScope(Builder $query, Request $request): void
    {
        $user = $request->user();

        if ($user !== null && $user->role === 'staff') {
            $query->where('created_by', $user->id);
        }
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
        return [
            IncomingTransaction::STATUS_PENDING => 'Pending',
            IncomingTransaction::STATUS_VERIFIED => 'Verified',
            IncomingTransaction::STATUS_COMPLETED => 'Completed',
        ];
    }

    private function outgoingStatusOptions(): array
    {
        return [
            OutgoingTransaction::STATUS_PENDING => 'Pending',
            OutgoingTransaction::STATUS_APPROVED => 'Approved',
            OutgoingTransaction::STATUS_SHIPPED => 'Shipped',
        ];
    }
}
