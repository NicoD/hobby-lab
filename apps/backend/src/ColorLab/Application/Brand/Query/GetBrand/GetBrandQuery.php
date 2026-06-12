<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Query\GetBrand;

final readonly class GetBrandQuery
{
    public function __construct(public string $handle)
    {
    }
}
