<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasIndexQueryHelpers
{
    /**
     * Sanitasi pagination dan batasi maksimal.
     */
    protected function resolvePerPage(Request $request, int $default = 10, int $max = 100): int
    {
        $perPage = (int) $request->query('per_page', $default);

        if ($perPage <= 0) {
            return $default;
        }

        return min($perPage, $max);
    }

    /**
     * Validasi sort & direction berdasarkan whitelist.
     */
    protected function resolveSortAndDirection(
        Request $request,
        array $allowedSorts,
        string $defaultSort,
        string $defaultDirection = 'asc'
    ): array {
        $sort = $request->query('sort', $defaultSort);
        $direction = strtolower($request->query('direction', $defaultDirection));

        // Sort yang tidak valid → fallback
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = $defaultSort;
        }

        // Direction aman (asc/desc)
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        return [$sort, $direction];
    }

    /**
     * Search LIKE di banyak kolom.
     */
    protected function applySearch(Builder $query, ?string $keyword, array $columns): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $q) use ($keyword, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', '%' . $keyword . '%');
            }
        });
    }

    /**
     * Apply filter berdasarkan map:
     *   'param_name' => 'column'
     * atau
     *   'param_name' => Closure
     */
    protected function applyFilters(Builder $query, Request $request, array $map): void
    {
        foreach ($map as $param => $handler) {
            $value = $request->query($param);

            if (is_array($value)) {
                $value = array_values(array_filter($value, static fn ($val) => $val !== null && $val !== ''));
                if (count($value) === 0) {
                    continue;
                }
            } elseif ($value === null || $value === '') {
                continue;
            }

            if (is_string($handler)) {
                if (is_array($value)) {
                    $query->whereIn($handler, $value);
                } else {
                    $query->where($handler, $value);
                }
                continue;
            }

            if (is_callable($handler)) {
                $handler($query, $value);
                continue;
            }
        }
    }

    /**
     * Shortcut untuk apply date_from & date_to filter pada kolom tertentu.
     */
    protected function applyDateRange(
        Builder $query,
        Request $request,
        string $column = 'created_at'
    ): void {
        $from = $request->query('date_from');
        $to   = $request->query('date_to');

        if (!empty($from)) {
            $query->whereDate($column, '>=', $from);
        }

        if (!empty($to)) {
            $query->whereDate($column, '<=', $to);
        }
    }
}
