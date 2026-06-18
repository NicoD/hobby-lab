<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

interface TransactionBoundary
{
    public function isTransactionActive(): bool;

    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;
}
