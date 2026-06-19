<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Query\ListBrands;

use App\ColorLab\Application\Brand\ReadModel\BrandListItemView;
use App\ColorLab\Application\Brand\ReadModel\BrandReadRepository;

final readonly class ListBrandsQueryHandler
{
    public function __construct(private BrandReadRepository $brands)
    {
    }

    /** @return list<BrandListItemView> */
    public function __invoke(ListBrandsQuery $listBrandsQuery): array
    {
        return $this->brands->list();
    }
}
