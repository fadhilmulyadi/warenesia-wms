<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumberGeneratorService
{

    public function generateDailySequence(
        string $table,
        string $column,
        string $prefix,
        int $padLength = 4,
        string $dateColumn = 'created_at'
    ): string {
        $callback = function () use ($table, $column, $prefix, $padLength, $dateColumn): string {
            $date = now()->toDateString();
            $datePart = now()->format('Ymd');
            $pattern = $prefix . '-' . $datePart . '-%';

            $lastNumber = DB::table($table)
                ->where($column, 'like', $pattern)
                ->when($dateColumn !== '', function ($query) use ($dateColumn, $date): void {
                    $query->whereDate($dateColumn, $date);
                })
                ->lockForUpdate()
                ->orderByDesc($column)
                ->value($column);

            $nextSequence = $this->extractSequence($lastNumber) + 1;

            return sprintf(
                '%s-%s-%s',
                $prefix,
                $datePart,
                str_pad((string) $nextSequence, $padLength, '0', STR_PAD_LEFT)
            );
        };

        return DB::transactionLevel() === 0
            ? DB::transaction($callback)
            : $callback();
    }

    public function generateSequentialNumber(
        string $table,
        string $column,
        string $prefix,
        int $padLength = 4,
        ?callable $constraint = null
    ): string {
        $callback = function () use ($table, $column, $prefix, $padLength, $constraint): string {
            $query = DB::table($table)
                ->where($column, 'like', $prefix . '-%');

            if ($constraint) {
                $constraint($query);
            }

            $lastNumber = $query
                ->lockForUpdate()
                ->orderByDesc($column)
                ->value($column);

            $nextSequence = $this->extractSequence($lastNumber) + 1;

            return sprintf(
                '%s-%s',
                $prefix,
                str_pad((string) $nextSequence, $padLength, '0', STR_PAD_LEFT)
            );
        };

        return DB::transactionLevel() === 0
            ? DB::transaction($callback)
            : $callback();
    }

    private function extractSequence(?string $number): int
    {
        if ($number === null || $number === '') {
            return 0;
        }

        $parts = explode('-', $number);
        $lastPart = array_pop($parts);

        return (int) $lastPart;
    }
}