<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Paint\Query\ListPaints;

use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintCriteria;
use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintListItemView;
use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintReadRepository;
use App\Shared\Application\Query\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPaintsQueryHandler
{
    public function __construct(private PaintReadRepository $paints)
    {
    }

    /** @return PaginatedResult<PaintListItemView> */
    public function __invoke(ListPaintsQuery $query): PaginatedResult
    {
        return $this->paints->list(new PaintCriteria(
            $query->ownedBy,
            $query->pagination,
            $query->sort,
            $query->search,
        ));
    }
}
