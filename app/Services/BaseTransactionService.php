<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use InvalidArgumentException;

abstract class BaseTransactionService
{
    public function __construct(
        protected readonly ActivityLogService $activityLogger,
        protected readonly StockAdjustmentService $stockAdjustments,
        protected readonly NumberGeneratorService $numberGenerator
    ) {}

    protected function extractItems(array $validatedData): array
    {
        $items = $validatedData['items'] ?? [];

        if (count($items) === 0) {
            throw new InvalidArgumentException('At least one product must be added to the transaction.');
        }

        return $items;
    }

    protected function formatDescription(User $user, string $action, string $subjectLabel): string
    {
        $userName = $user->name ?? 'Unknown';

        return sprintf(
            'User "%s" melakukan "%s" pada "%s".',
            $userName,
            strtoupper($action),
            $subjectLabel
        );
    }

    protected function logActivity(?User $user, string $action, string $description, object $subject): void
    {
        $this->activityLogger->log(
            $user,
            $action,
            $description,
            $subject
        );
    }

    protected function runWithNumberRetry(callable $callback): mixed
    {
        $attempts = 0;
        $maxAttempts = 3;

        do {
            try {
                return $callback();
            } catch (QueryException $exception) {
                $attempts++;

                if (! $this->isUniqueConstraintViolation($exception) || $attempts >= $maxAttempts) {
                    throw $exception;
                }
            }
        } while ($attempts < $maxAttempts);

        return $callback();
    }

    protected function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000';
    }
}
