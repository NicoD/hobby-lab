<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\ReadModel;

use App\Shared\Application\Query\Pagination;
use App\Shared\Application\Query\SortOrder;

final readonly class ColorCriteria
{
    public function __construct(
        public string $ownedBy,
        public Pagination $pagination,
        public SortOrder $sort,
        public ?string $search = null,
    ) {
    }
}
