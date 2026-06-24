<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Color\Query\ListColors;

use App\ColorLab\Catalog\Application\Color\ReadModel\ColorListItemView;
use App\Shared\Application\Bus\Query;
use App\Shared\Application\Query\PaginatedResult;
use App\Shared\Application\Query\Pagination;
use App\Shared\Application\Query\SortOrder;

/** @implements Query<PaginatedResult<ColorListItemView>> */
final readonly class ListColorsQuery implements Query
{
    public function __construct(
        public string $ownedBy,
        public Pagination $pagination,
        public SortOrder $sort,
        public ?string $search = null,
    ) {
    }
}
