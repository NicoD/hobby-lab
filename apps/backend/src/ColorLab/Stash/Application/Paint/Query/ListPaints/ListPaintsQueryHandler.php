<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Application\Paint\Query\ListPaints;

use App\ColorLab\Stash\Application\Paint\ReadModel\PaintListItemView;
use App\ColorLab\Stash\Application\Paint\ReadModel\PaintReadRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPaintsQueryHandler
{
    public function __construct(private PaintReadRepository $paints)
    {
    }

    /** @return list<PaintListItemView> */
    public function __invoke(ListPaintsQuery $_query): array
    {
        return $this->paints->list();
    }
}
