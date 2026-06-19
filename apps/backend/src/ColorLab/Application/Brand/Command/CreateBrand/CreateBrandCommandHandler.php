<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Command\CreateBrand;

use App\ColorLab\Domain\Model\Brand;
use App\ColorLab\Domain\Model\BrandHandle;
use App\ColorLab\Domain\Repository\BrandRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;

final readonly class CreateBrandCommandHandler
{
    public function __construct(
        private BrandRepository $brands,
        private HandleGeneratorFactory $handleGeneratorFactory,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreateBrandCommand $command): void
    {
        $this->transactionManager->execute(function () use ($command): Brand {
            $brand = Brand::create(
                $command->name,
                new UserId($command->ownedBy),
                $this->handleGeneratorFactory->create(
                    fn (string $h): bool => $this->brands->findByHandle(new BrandHandle($h)) instanceof Brand
                ),
            );

            $this->brands->save($brand);

            return $brand;
        });
    }
}
