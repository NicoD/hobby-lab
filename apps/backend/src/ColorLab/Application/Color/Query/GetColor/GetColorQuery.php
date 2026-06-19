<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Query\GetColor;

final readonly class GetColorQuery
{
    public function __construct(public string $handle)
    {
    }
}
