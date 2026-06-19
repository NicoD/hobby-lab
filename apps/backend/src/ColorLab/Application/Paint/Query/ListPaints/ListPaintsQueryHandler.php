<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Query\ListPaints;

use App\ColorLab\Application\Paint\ReadModel\PaintListItemView;
use App\ColorLab\Application\Paint\ReadModel\PaintReadRepository;

final class ListPaintsQueryHandler
{
    public function __construct(private readonly PaintReadRepository $paints)
    {
    }

    /** @return list<PaintListItemView> */
    public function __invoke(ListPaintsQuery $query): array
    {
        return $this->paints->list();
    }
}
