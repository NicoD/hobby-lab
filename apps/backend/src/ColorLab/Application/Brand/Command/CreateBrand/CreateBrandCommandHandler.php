<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\Command\CreateBrand;

use App\ColorLab\Domain\Model\Brand;
use App\ColorLab\Domain\Model\BrandHandle;
use App\ColorLab\Domain\Repository\BrandRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateBrandCommandHandler
{
    public function __construct(
        private BrandRepository $brands,
        private HandleGeneratorFactory $handleGeneratorFactory,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreateBrandCommand $command): BrandHandle
    {
        $brand = null;

        $this->transactionManager->execute(function () use ($command, &$brand): Brand {
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

        \assert($brand instanceof Brand);

        return $brand->handle;
    }
}
