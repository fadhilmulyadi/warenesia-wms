<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(private readonly ?string $productName = null)
    {
        $message = $productName !== null
            ? 'Insufficient stock for product: ' . $productName
            : 'Insufficient stock for one or more products.';

        parent::__construct($message);
    }

    public function productName(): ?string
    {
        return $this->productName;
    }
}
