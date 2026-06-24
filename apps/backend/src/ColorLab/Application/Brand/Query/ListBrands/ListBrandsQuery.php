<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Query\ListBrands;

use App\ColorLab\Application\Brand\ReadModel\BrandListItemView;
use App\Shared\Application\Bus\Query;
use App\Shared\Application\Query\PaginatedResult;
use App\Shared\Application\Query\Pagination;
use App\Shared\Application\Query\SortOrder;

/** @implements Query<PaginatedResult<BrandListItemView>> */
final readonly class ListBrandsQuery implements Query
{
    public function __construct(
        public string $ownedBy,
        public Pagination $pagination,
        public SortOrder $sort,
        public ?string $search = null,
    ) {
    }
}
