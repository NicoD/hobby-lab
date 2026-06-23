<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\ListPaintReferences;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceCriteria;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPaintReferencesQueryHandler
{
    public function __construct(private PaintReferenceReadRepository $paintReferences)
    {
    }

    /** @return list<PaintReferenceListItemView> */
    public function __invoke(ListPaintReferencesQuery $query): array
    {
        return $this->paintReferences->list(new PaintReferenceCriteria(
            $query->ownedBy,
            $query->brandHandle,
            $query->rangeHandle,
            $query->paintTypeHandle,
            $query->colorHandle,
            $query->search,
        ));
    }
}
