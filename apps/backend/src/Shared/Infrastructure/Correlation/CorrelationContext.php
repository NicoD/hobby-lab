<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Correlation;

final class CorrelationContext
{
    private ?string $correlationId = null;

    public function set(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }

    public function clear(): void
    {
        $this->correlationId = null;
    }

    public function get(): ?string
    {
        return $this->correlationId;
    }
}
