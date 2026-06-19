<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\ListPaintReferences;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;

final readonly class ListPaintReferencesQueryHandler
{
    public function __construct(private PaintReferenceReadRepository $paintReferences)
    {
    }

    /** @return list<PaintReferenceListItemView> */
    public function __invoke(): array
    {
        return $this->paintReferences->list();
    }
}
