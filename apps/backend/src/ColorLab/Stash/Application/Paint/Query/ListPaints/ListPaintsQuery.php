<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Application\Paint\Query\ListPaints;

use App\ColorLab\Stash\Application\Paint\ReadModel\PaintListItemView;
use App\Shared\Application\Bus\Query;

/** @implements Query<list<PaintListItemView>> */
final readonly class ListPaintsQuery implements Query
{
}
