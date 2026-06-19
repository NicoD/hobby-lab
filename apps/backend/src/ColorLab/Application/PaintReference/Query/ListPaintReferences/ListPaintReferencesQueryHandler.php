<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\ListPaintReferences;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;

final class ListPaintReferencesQueryHandler
{
    public function __construct(private readonly PaintReferenceReadRepository $paintReferences)
    {
    }

    /** @return list<PaintReferenceListItemView> */
    public function __invoke(ListPaintReferencesQuery $query): array
    {
        return $this->paintReferences->list();
    }
}
