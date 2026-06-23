<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Query\ListColors;

use App\ColorLab\Application\Color\ReadModel\ColorListItemView;
use App\Shared\Application\Bus\Query;

/** @implements Query<list<ColorListItemView>> */
final readonly class ListColorsQuery implements Query
{
    public function __construct(
        public ?string $search = null,
    ) {
    }
}
