<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Command\CreatePaintReference;

use App\ColorLab\Domain\Model\BrandHandle;
use App\ColorLab\Domain\Model\ColorHandle;
use App\ColorLab\Domain\Model\PaintReference;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\ColorLab\Domain\Model\PaintTypeHandle;
use App\ColorLab\Domain\Model\RangeHandle;
use App\ColorLab\Domain\Repository\PaintReferenceRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;

final readonly class CreatePaintReferenceCommandHandler
{
    public function __construct(
        private PaintReferenceRepository $paintReferences,
        private HandleGeneratorFactory $handleGeneratorFactory,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreatePaintReferenceCommand $command): void
    {
        $this->transactionManager->execute(function () use ($command): PaintReference {
            $ref = PaintReference::create(
                $command->name,
                new BrandHandle($command->brandHandle),
                new RangeHandle($command->rangeHandle),
                new PaintTypeHandle($command->paintTypeHandle),
                null !== $command->colorHandle ? new ColorHandle($command->colorHandle) : null,
                new UserId($command->ownedBy),
                $this->handleGeneratorFactory->create(
                    fn (string $h): bool => $this->paintReferences->findByHandle(new PaintReferenceHandle($h)) instanceof \App\ColorLab\Domain\Model\PaintReference
                ),
            );

            $this->paintReferences->save($ref);

            return $ref;
        });
    }
}
