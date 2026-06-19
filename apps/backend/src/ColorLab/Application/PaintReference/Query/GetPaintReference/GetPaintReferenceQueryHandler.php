<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\GetPaintReference;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceView;

final class GetPaintReferenceQueryHandler
{
    public function __construct(private readonly PaintReferenceReadRepository $paintReferences)
    {
    }

    public function __invoke(GetPaintReferenceQuery $query): ?PaintReferenceView
    {
        return $this->paintReferences->findByHandle($query->handle);
    }
}
