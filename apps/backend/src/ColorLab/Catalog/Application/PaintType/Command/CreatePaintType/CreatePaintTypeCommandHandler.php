<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\PaintType\Command\CreatePaintType;

use App\ColorLab\Catalog\Domain\PaintType\PaintType;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreatePaintTypeCommandHandler
{
    public function __construct(
        private PaintTypeRepository $paintTypes,
        private HandleGeneratorFactory $handleGeneratorFactory,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreatePaintTypeCommand $command): PaintTypeHandle
    {
        $paintType = null;

        $this->transactionManager->execute(function () use ($command, &$paintType): PaintType {
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

        \assert($paintType instanceof PaintType);

        return $paintType->handle;
    }
}
