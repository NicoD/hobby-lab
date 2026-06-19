<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Query\GetPaintType;

final readonly class GetPaintTypeQuery
{
    public function __construct(public string $handle)
    {
    }
}
