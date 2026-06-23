<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Query\ListPaintTypes;

use App\ColorLab\Application\PaintType\ReadModel\PaintTypeListItemView;
use App\ColorLab\Application\PaintType\ReadModel\PaintTypeReadRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPaintTypesQueryHandler
{
    public function __construct(private PaintTypeReadRepository $paintTypes)
    {
    }

    /** @return list<PaintTypeListItemView> */
    public function __invoke(ListPaintTypesQuery $_query): array
    {
        return $this->paintTypes->list();
    }
}
