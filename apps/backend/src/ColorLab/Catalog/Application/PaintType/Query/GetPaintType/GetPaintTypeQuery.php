<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\PaintType\Query\GetPaintType;

use App\ColorLab\Catalog\Application\PaintType\ReadModel\PaintTypeView;
use App\Shared\Application\Bus\Query;

/** @implements Query<PaintTypeView|null> */
final readonly class GetPaintTypeQuery implements Query
{
    public function __construct(public string $handle)
    {
    }
}
