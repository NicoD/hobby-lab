<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Query\GetColor;

use App\ColorLab\Application\Color\ReadModel\ColorView;
use App\Shared\Application\Bus\Query;

/** @implements Query<ColorView|null> */
final readonly class GetColorQuery implements Query
{
    public function __construct(public string $handle)
    {
    }
}
