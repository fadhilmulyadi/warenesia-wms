<?php

namespace App\Services;

class GeneratorService
{
    /**
     * Generate a sequential transaction number for a given model and column.
     */
    public static function generateDailySequence(
        string $modelClass,
        string $column,
        string $prefix,
        int $padLength = 4,
        string $timestampColumn = 'created_at'
    ): string {
        $datePart = now()->format('Ymd');

        $lastRecord = $modelClass::query()
            ->whereDate($timestampColumn, now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $lastSequence = 0;

        if ($lastRecord !== null) {
            $parts = explode('-', (string) $lastRecord->{$column});
            $lastSequence = isset($parts[2]) ? (int) $parts[2] : 0;
        }

        $nextSequence = $lastSequence + 1;
        $sequencePart = str_pad((string) $nextSequence, $padLength, '0', STR_PAD_LEFT);

        return $prefix . '-' . $datePart . '-' . $sequencePart;
    }
}
