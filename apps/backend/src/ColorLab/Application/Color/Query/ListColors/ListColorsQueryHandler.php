<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Query\ListColors;

use App\ColorLab\Application\Color\ReadModel\ColorListItemView;
use App\ColorLab\Application\Color\ReadModel\ColorReadRepository;

final readonly class ListColorsQueryHandler
{
    public function __construct(private ColorReadRepository $colors)
    {
    }

    /** @return list<ColorListItemView> */
    public function __invoke(ListColorsQuery $listColorsQuery): array
    {
        return $this->colors->list();
    }
}
