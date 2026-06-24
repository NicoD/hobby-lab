<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\ListPaintReferences;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\Shared\Application\Bus\Query;
use App\Shared\Application\Query\PaginatedResult;
use App\Shared\Application\Query\Pagination;
use App\Shared\Application\Query\SortOrder;

/** @implements Query<PaginatedResult<PaintReferenceListItemView>> */
final readonly class ListPaintReferencesQuery implements Query
{
    public function __construct(
        public string $ownedBy,
        public Pagination $pagination,
        public SortOrder $sort,
        public ?string $search = null,
    ) {
    }
}
