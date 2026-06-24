<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Query\ListColors;

use App\ColorLab\Application\Color\ReadModel\ColorCriteria;
use App\ColorLab\Application\Color\ReadModel\ColorListItemView;
use App\ColorLab\Application\Color\ReadModel\ColorReadRepository;
use App\Shared\Application\Query\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListColorsQueryHandler
{
    public function __construct(private ColorReadRepository $colors)
    {
    }

    /** @return PaginatedResult<ColorListItemView> */
    public function __invoke(ListColorsQuery $query): PaginatedResult
    {
        return $this->colors->list(new ColorCriteria(
            $query->ownedBy,
            $query->pagination,
            $query->sort,
            $query->search,
        ));
    }
}
