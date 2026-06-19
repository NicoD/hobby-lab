<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Query\ListPaints;

use App\ColorLab\Application\Paint\ReadModel\PaintListItemView;
use App\ColorLab\Application\Paint\ReadModel\PaintReadRepository;

final readonly class ListPaintsQueryHandler
{
    public function __construct(private PaintReadRepository $paints)
    {
    }

    /** @return list<PaintListItemView> */
    public function __invoke(ListPaintsQuery $listPaintsQuery): array
    {
        return $this->paints->list();
    }
}
