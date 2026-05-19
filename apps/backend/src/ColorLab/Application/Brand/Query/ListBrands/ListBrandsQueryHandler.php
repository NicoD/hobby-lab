<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Query\ListBrands;

use App\ColorLab\Application\Brand\ReadModel\BrandListItemView;
use App\ColorLab\Application\Brand\ReadModel\BrandReadRepository;

final class ListBrandsQueryHandler
{
    public function __construct(private readonly BrandReadRepository $brands) {}

    /** @return BrandListItemView[] */
    public function __invoke(ListBrandsQuery $query): array
    {
        return $this->brands->list();
    }
}
