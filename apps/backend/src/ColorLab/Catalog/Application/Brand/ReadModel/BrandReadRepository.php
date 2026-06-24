<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Brand\ReadModel;

use App\Shared\Application\Query\PaginatedResult;

interface BrandReadRepository
{
    /** @return PaginatedResult<BrandListItemView> */
    public function list(BrandCriteria $criteria): PaginatedResult;

    public function findByHandle(string $handle): ?BrandView;
}
