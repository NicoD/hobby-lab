<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Brand\Query\GetBrand;

use App\ColorLab\Catalog\Application\Brand\ReadModel\BrandView;
use App\Shared\Application\Bus\Query;

/** @implements Query<BrandView|null> */
final readonly class GetBrandQuery implements Query
{
    public function __construct(public string $handle)
    {
    }
}
