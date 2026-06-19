<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Query\ListPaintTypes;

use App\ColorLab\Application\PaintType\ReadModel\PaintTypeListItemView;
use App\ColorLab\Application\PaintType\ReadModel\PaintTypeReadRepository;

final class ListPaintTypesQueryHandler
{
    public function __construct(private readonly PaintTypeReadRepository $paintTypes)
    {
    }

    /** @return list<PaintTypeListItemView> */
    public function __invoke(ListPaintTypesQuery $query): array
    {
        return $this->paintTypes->list();
    }
}
