<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Query\GetPaintType;

use App\ColorLab\Application\PaintType\ReadModel\PaintTypeReadRepository;
use App\ColorLab\Application\PaintType\ReadModel\PaintTypeView;

final class GetPaintTypeQueryHandler
{
    public function __construct(private readonly PaintTypeReadRepository $paintTypes)
    {
    }

    public function __invoke(GetPaintTypeQuery $query): ?PaintTypeView
    {
        return $this->paintTypes->findByHandle($query->handle);
    }
}
