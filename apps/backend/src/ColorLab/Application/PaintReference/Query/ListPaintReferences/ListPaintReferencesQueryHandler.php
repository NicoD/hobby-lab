<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\ListPaintReferences;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceCriteria;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;
use App\Shared\Application\Query\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPaintReferencesQueryHandler
{
    public function __construct(private PaintReferenceReadRepository $paintReferences)
    {
    }

    /** @return PaginatedResult<PaintReferenceListItemView> */
    public function __invoke(ListPaintReferencesQuery $query): PaginatedResult
    {
        return $this->paintReferences->list(new PaintReferenceCriteria(
            $query->ownedBy,
            $query->pagination,
            $query->sort,
            $query->search,
        ));
    }
}
