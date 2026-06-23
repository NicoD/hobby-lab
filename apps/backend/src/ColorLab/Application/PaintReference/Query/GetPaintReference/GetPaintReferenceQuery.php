<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\GetPaintReference;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceView;
use App\Shared\Application\Bus\Query;

/** @implements Query<PaintReferenceView|null> */
final readonly class GetPaintReferenceQuery implements Query
{
    public function __construct(public string $handle)
    {
    }
}
