<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Query\ListBrands;

use App\ColorLab\Application\Brand\ReadModel\BrandCriteria;
use App\ColorLab\Application\Brand\ReadModel\BrandListItemView;
use App\ColorLab\Application\Brand\ReadModel\BrandReadRepository;
use App\Shared\Application\Query\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListBrandsQueryHandler
{
    public function __construct(private BrandReadRepository $brands)
    {
    }

    /** @return PaginatedResult<BrandListItemView> */
    public function __invoke(ListBrandsQuery $query): PaginatedResult
    {
        return $this->brands->list(new BrandCriteria(
            $query->ownedBy,
            $query->pagination,
            $query->sort,
            $query->search,
        ));
    }
}
