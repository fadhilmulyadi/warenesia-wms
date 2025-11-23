<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionReportRequest;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\RestockOrder;
use App\Support\CsvExporter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const EXPORT_CHUNK_SIZE = 200;

    public function transactions(TransactionReportRequest $request): View
    {
        $filters = $request->filters();
        $dateRange = $this->buildDateRange($filters);

        $purchaseSummary = $this->emptySummary();
        $salesSummary = $this->emptySummary();
        $restockSummary = $this->emptySummary();
        $recentPurchases = collect();
        $recentSales = collect();
        $recentRestocks = collect();

        if ($this->shouldIncludeType($filters['transaction_type'], 'purchases')) {
            $incomingQuery = $this->buildIncomingQuery($filters, $dateRange);
            $purchaseSummary = $this->summarizeTransactions(clone $incomingQuery, 'total_amount');

            $recentPurchases = $incomingQuery
                ->with(['supplier'])
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        }

        if ($this->shouldIncludeType($filters['transaction_type'], 'sales')) {
            $salesQuery = $this->buildSalesQuery($filters, $dateRange);
            $salesSummary = $this->summarizeTransactions(clone $salesQuery, 'total_amount');

            $recentSales = $salesQuery
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        }

        if ($this->shouldIncludeType($filters['transaction_type'], 'restocks')) {
            $restockQuery = $this->buildRestockQuery($filters, $dateRange);
            $restockSummary = $this->summarizeTransactions(clone $restockQuery, 'total_amount');

            $recentRestocks = $restockQuery
                ->with(['supplier'])
                ->orderByDesc('order_date')
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        }

        $netFlow = $salesSummary['total_amount'] - $purchaseSummary['total_amount'];

        $statusOptions = $this->statusOptionsForSelect($filters['transaction_type']);

        return view('admin.reports.transactions', compact(
            'filters',
            'dateRange',
            'purchaseSummary',
            'salesSummary',
            'restockSummary',
            'netFlow',
            'recentPurchases',
            'recentSales',
            'recentRestocks',
            'statusOptions'
        ));
    }

    public function exportTransactions(TransactionReportRequest $request): StreamedResponse
    {
        $filters = $request->filters();
        $dateRange = $this->buildDateRange($filters);

        $incomingQuery = $this->buildIncomingQuery($filters, $dateRange)->with(['supplier', 'createdBy']);
        $salesQuery = $this->buildSalesQuery($filters, $dateRange)->with(['createdBy']);
        $restockQuery = $this->buildRestockQuery($filters, $dateRange)->with(['supplier', 'createdBy']);

        $selectedTypes = $this->selectedTypes($filters['transaction_type']);
        $filename = 'transactions-' . now()->format('Ymd-His') . '.csv';

        return CsvExporter::stream($filename, function (\SplFileObject $output) use ($selectedTypes, $incomingQuery, $salesQuery, $restockQuery): void {
            $output->fputcsv([
                'Type',
                'Date',
                'Number',
                'Counterparty',
                'Status',
                'Total Amount',
                'Created By',
            ]);

            $exportMap = [
                'purchases' => [
                    'query' => $incomingQuery,
                    'date_column' => 'transaction_date',
                    'reference_column' => 'transaction_number',
                    'status_type' => 'purchases',
                    'type_label' => 'Purchase',
                    'counterparty' => static function ($transaction): string {
                        return optional($transaction->supplier)->name ?? '-';
                    },
                    'creator' => static function ($transaction): string {
                        return optional($transaction->createdBy)->name ?? '';
                    },
                ],
                'sales' => [
                    'query' => $salesQuery,
                    'date_column' => 'transaction_date',
                    'reference_column' => 'transaction_number',
                    'status_type' => 'sales',
                    'type_label' => 'Sale',
                    'counterparty' => static function ($transaction): string {
                        return (string) $transaction->customer_name;
                    },
                    'creator' => static function ($transaction): string {
                        return optional($transaction->createdBy)->name ?? '';
                    },
                ],
                'restocks' => [
                    'query' => $restockQuery,
                    'date_column' => 'order_date',
                    'reference_column' => 'po_number',
                    'status_type' => 'restocks',
                    'type_label' => 'Restock',
                    'counterparty' => static function ($transaction): string {
                        return optional($transaction->supplier)->name ?? '-';
                    },
                    'creator' => static function ($transaction): string {
                        return optional($transaction->createdBy)->name ?? '';
                    },
                ],
            ];

            foreach ($selectedTypes as $type) {
                if (! isset($exportMap[$type])) {
                    continue;
                }

                $config = $exportMap[$type];
                $query = $config['query']
                    ->orderBy($config['date_column'])
                    ->orderBy('id');

                $query->chunk(self::EXPORT_CHUNK_SIZE, function (Collection $rows) use ($output, $config): void {
                    foreach ($rows as $row) {
                        $dateValue = $row->{$config['date_column']};
                        $formattedDate = $dateValue instanceof Carbon
                            ? $dateValue->format('Y-m-d')
                            : (string) $dateValue;

                        $output->fputcsv([
                            $config['type_label'],
                            $formattedDate,
                            $row->{$config['reference_column']},
                            $config['counterparty']($row),
                            $this->statusLabel((string) $row->status, $config['status_type']),
                            (float) $row->total_amount,
                            $config['creator']($row),
                        ]);
                    }
                });
            }
        });
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{from: Carbon, to: Carbon, preset: string}
     */
    private function buildDateRange(array $filters): array
    {
        $preset = $filters['date_preset'] ?: 'this_month';
        $now = now();

        $dateFrom = $now->copy()->startOfMonth();
        $dateTo = $now->copy()->endOfMonth();

        switch ($preset) {
            case 'today':
                $dateFrom = $now->copy()->startOfDay();
                $dateTo = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $dateFrom = $now->copy()->subDay()->startOfDay();
                $dateTo = $now->copy()->subDay()->endOfDay();
                break;
            case 'last_7_days':
                $dateFrom = $now->copy()->subDays(6)->startOfDay();
                $dateTo = $now->copy()->endOfDay();
                break;
            case 'last_month':
                $dateFrom = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $dateTo = $now->copy()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'this_year':
                $dateFrom = $now->copy()->startOfYear();
                $dateTo = $now->copy()->endOfDay();
                break;
            case 'custom':
                if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                    $dateFrom = Carbon::parse($filters['date_from'])->startOfDay();
                    $dateTo = Carbon::parse($filters['date_to'])->endOfDay();
                }
                break;
            case 'this_month':
            default:
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy()->endOfMonth();
                break;
        }

        return [
            'from' => $dateFrom,
            'to' => $dateTo,
            'preset' => $preset,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @param array{from: Carbon, to: Carbon} $dateRange
     */
    private function buildIncomingQuery(array $filters, array $dateRange): Builder
    {
        $query = IncomingTransaction::query()
            ->whereBetween('transaction_date', [$dateRange['from'], $dateRange['to']]);

        if ($this->shouldFilterStatus($filters, 'purchases')) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $filters
     * @param array{from: Carbon, to: Carbon} $dateRange
     */
    private function buildSalesQuery(array $filters, array $dateRange): Builder
    {
        $query = OutgoingTransaction::query()
            ->whereBetween('transaction_date', [$dateRange['from'], $dateRange['to']]);

        if ($this->shouldFilterStatus($filters, 'sales')) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $filters
     * @param array{from: Carbon, to: Carbon} $dateRange
     */
    private function buildRestockQuery(array $filters, array $dateRange): Builder
    {
        $query = RestockOrder::query()
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']]);

        if ($this->shouldFilterStatus($filters, 'restocks')) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    private function summarizeTransactions(Builder $query, string $amountColumn): array
    {
        $count = (int) $query->count();
        $total = (float) $query->sum($amountColumn);
        $average = $count > 0 ? $total / $count : null;

        return [
            'count' => $count,
            'total_amount' => $total,
            'average_amount' => $average,
        ];
    }

    private function emptySummary(): array
    {
        return [
            'count' => 0,
            'total_amount' => 0.0,
            'average_amount' => null,
        ];
    }

    private function shouldFilterStatus(array $filters, string $type): bool
    {
        if (! $this->shouldIncludeType($filters['transaction_type'], $type)) {
            return false;
        }

        if (empty($filters['status'])) {
            return false;
        }

        $allowedStatuses = array_keys($this->statusOptionsForType($type));

        return in_array($filters['status'], $allowedStatuses, true);
    }

    private function shouldIncludeType(string $filterValue, string $type): bool
    {
        return $filterValue === 'all' || $filterValue === $type;
    }

    /**
     * @param array<string, array<string, string>> $statusOptions
     */
    private function mergedStatusOptions(array $statusOptions): array
    {
        $merged = [];

        foreach ($statusOptions as $options) {
            foreach ($options as $value => $label) {
                $merged[$value] = $label;
            }
        }

        return $merged;
    }

    private function statusOptions(): array
    {
        return [
            'purchases' => [
                IncomingTransaction::STATUS_PENDING => 'Pending',
                IncomingTransaction::STATUS_VERIFIED => 'Verified',
                IncomingTransaction::STATUS_COMPLETED => 'Completed',
                IncomingTransaction::STATUS_REJECTED => 'Rejected',
            ],
            'sales' => [
                OutgoingTransaction::STATUS_PENDING => 'Pending',
                OutgoingTransaction::STATUS_APPROVED => 'Approved',
                OutgoingTransaction::STATUS_SHIPPED => 'Shipped',
            ],
            'restocks' => RestockOrder::statusOptions(),
        ];
    }

    private function statusOptionsForType(string $transactionType): array
    {
        $statusOptions = $this->statusOptions();

        return match ($transactionType) {
            'purchases' => $statusOptions['purchases'],
            'sales' => $statusOptions['sales'],
            'restocks' => $statusOptions['restocks'],
            default => $this->mergedStatusOptions($statusOptions),
        };
    }

    private function statusOptionsForSelect(string $transactionType): array
    {
        return $this->statusOptionsForType($transactionType);
    }

    private function statusLabel(string $status, string $transactionType): string
    {
        $options = $this->statusOptionsForType($transactionType);

        return $options[$status] ?? ucfirst($status);
    }

    private function selectedTypes(string $transactionType): array
    {
        if ($transactionType === 'all') {
            return ['purchases', 'sales', 'restocks'];
        }

        return [$transactionType];
    }
}
