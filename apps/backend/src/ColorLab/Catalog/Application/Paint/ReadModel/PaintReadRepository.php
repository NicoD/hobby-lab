<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Paint\ReadModel;

use App\Shared\Application\Query\PaginatedResult;

interface PaintReadRepository
{
    /** @return PaginatedResult<PaintListItemView> */
    public function list(PaintCriteria $criteria): PaginatedResult;

    public function findByHandle(string $handle): ?PaintView;
}
