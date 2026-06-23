<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Query\ListPaintTypes;

use App\ColorLab\Application\PaintType\ReadModel\PaintTypeListItemView;
use App\Shared\Application\Bus\Query;

/** @implements Query<list<PaintTypeListItemView>> */
final readonly class ListPaintTypesQuery implements Query
{
}
