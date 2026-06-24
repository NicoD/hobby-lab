<?php

declare(strict_types=1);

namespace App\Shared\Application\Query;

final class Pagination
{
    public int $offset {
        get => ($this->page - 1) * $this->limit;
    }

    public function __construct(
        public readonly int $page,
        public readonly int $limit,
    ) {
    }
}
