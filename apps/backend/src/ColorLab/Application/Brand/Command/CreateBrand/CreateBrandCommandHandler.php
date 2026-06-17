<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Command\CreateBrand;

use App\ColorLab\Domain\Model\Brand;
use App\ColorLab\Domain\Repository\BrandRepository;
use App\Shared\Domain\Model\UserId;

final class CreateBrandCommandHandler
{
    public function __construct(private readonly BrandRepository $brands)
    {
    }

    public function __invoke(CreateBrandCommand $command): void
    {
        $brand = Brand::create(
            $command->name,
            new UserId($command->ownedBy),
            [],
        );

        $this->brands->save($brand);
    }
}
