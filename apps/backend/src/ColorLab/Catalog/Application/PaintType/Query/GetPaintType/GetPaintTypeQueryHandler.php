<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\PaintType\Query\GetPaintType;

use App\ColorLab\Catalog\Application\PaintType\ReadModel\PaintTypeReadRepository;
use App\ColorLab\Catalog\Application\PaintType\ReadModel\PaintTypeView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetPaintTypeQueryHandler
{
    public function __construct(private PaintTypeReadRepository $paintTypes)
    {
    }

    public function __invoke(GetPaintTypeQuery $query): ?PaintTypeView
    {
        return $this->paintTypes->findByHandle($query->handle);
    }
}
