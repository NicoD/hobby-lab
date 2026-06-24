<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Paint\Query\ListPaints;

use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintListItemView;
use App\Shared\Application\Bus\Query;
use App\Shared\Application\Query\PaginatedResult;
use App\Shared\Application\Query\Pagination;
use App\Shared\Application\Query\SortOrder;

/** @implements Query<PaginatedResult<PaintListItemView>> */
final readonly class ListPaintsQuery implements Query
{
    public function __construct(
        public string $ownedBy,
        public Pagination $pagination,
        public SortOrder $sort,
        public ?string $search = null,
    ) {
    }
}
