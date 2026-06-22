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

    public function __invoke(CreatePaintReferenceCommand $command): PaintReferenceHandle
    {
        $ref = null;

        $this->transactionManager->execute(function () use ($command, &$ref): PaintReference {
            $ref = PaintReference::create(
                $command->name,
                wrap($command->brandHandle, BrandHandle::class),
                wrap($command->rangeHandle, RangeHandle::class),
                wrap($command->paintTypeHandle, PaintTypeHandle::class),
                wrap($command->colorHandle, ColorHandle::class),
                new UserId($command->ownedBy),
                $this->handleGeneratorFactory->create(
                    fn (string $h): bool => $this->paintReferences->findByHandle(new PaintReferenceHandle($h)) instanceof PaintReference
                ),
            );

            $this->paintReferences->save($ref);

            return $ref;
        });

        \assert($ref instanceof PaintReference);

        return $ref->handle;
    }
}
