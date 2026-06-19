<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Command\CreatePaintType;

use App\ColorLab\Domain\Model\PaintType;
use App\ColorLab\Domain\Model\PaintTypeHandle;
use App\ColorLab\Domain\Repository\PaintTypeRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;

final readonly class CreatePaintTypeCommandHandler
{
    public function __construct(
        private PaintTypeRepository $paintTypes,
        private HandleGeneratorFactory $handleGeneratorFactory,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreatePaintTypeCommand $command): void
    {
        $this->transactionManager->execute(function () use ($command): PaintType {
            $paintType = PaintType::create(
                $command->name,
                new UserId($command->ownedBy),
                $this->handleGeneratorFactory->create(
                    fn (string $h): bool => $this->paintTypes->findByHandle(new PaintTypeHandle($h)) instanceof PaintType
                ),
            );

            $this->paintTypes->save($paintType);

            return $paintType;
        });
    }
}
