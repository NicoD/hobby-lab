<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Query\GetBrand;

use App\ColorLab\Application\Brand\ReadModel\BrandReadRepository;
use App\ColorLab\Application\Brand\ReadModel\BrandView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetBrandQueryHandler
{
    public function __construct(private BrandReadRepository $brands)
    {
    }

    public function __invoke(GetBrandQuery $query): ?BrandView
    {
        return $this->brands->findByHandle($query->handle);
    }
}
