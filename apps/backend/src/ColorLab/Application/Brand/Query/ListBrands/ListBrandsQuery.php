<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Query\ListBrands;

use App\ColorLab\Application\Brand\ReadModel\BrandListItemView;
use App\Shared\Application\Bus\Query;

/** @implements Query<list<BrandListItemView>> */
final readonly class ListBrandsQuery implements Query
{
}
