<?php

declare(strict_types=1);

namespace App\Shared\Application\Query;

final class SortOrder
{
    public bool $isAsc {
        get => 'asc' === $this->direction;
    }

    public function __construct(
        public readonly string $field,
        public readonly string $direction,
    ) {
    }
}
