<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Command\CreateBrand;

use App\ColorLab\Domain\Brand;
use App\ColorLab\Domain\Repository\BrandRepository;
use App\ColorLab\Domain\ValueObject\BrandHandle;
use App\Shared\Domain\ValueObject\UserId;

final class CreateBrandCommandHandler
{
    public function __construct(private readonly BrandRepository $brands) {}

    public function __invoke(CreateBrandCommand $command): void
    {
        $brand = new Brand(
            BrandHandle::fromString($command->handle),
            $command->name,
            UserId::fromString($command->ownedBy),
        );

        $this->brands->save($brand);
    }
}
