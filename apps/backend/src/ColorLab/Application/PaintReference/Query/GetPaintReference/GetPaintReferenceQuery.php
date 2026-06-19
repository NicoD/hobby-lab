<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\GetPaintReference;

final readonly class GetPaintReferenceQuery
{
    public function __construct(public string $handle)
    {
    }
}
