<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\ReadModel;

use App\Shared\Application\Query\PaginatedResult;

interface PaintReferenceReadRepository
{
    /** @return PaginatedResult<PaintReferenceListItemView> */
    public function list(PaintReferenceCriteria $criteria): PaginatedResult;

    public function findByHandle(string $handle): ?PaintReferenceView;
}
