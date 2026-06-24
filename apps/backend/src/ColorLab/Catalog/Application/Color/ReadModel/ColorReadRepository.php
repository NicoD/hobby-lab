<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Color\ReadModel;

use App\Shared\Application\Query\PaginatedResult;

interface ColorReadRepository
{
    /** @return PaginatedResult<ColorListItemView> */
    public function list(ColorCriteria $criteria): PaginatedResult;

    public function findByHandle(string $handle): ?ColorView;
}
