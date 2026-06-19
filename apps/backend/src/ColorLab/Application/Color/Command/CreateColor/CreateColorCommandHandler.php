<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Command\CreateColor;

use App\ColorLab\Domain\Model\Color;
use App\ColorLab\Domain\Model\ColorHandle;
use App\ColorLab\Domain\Repository\ColorRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;

final class CreateColorCommandHandler
{
    public function __construct(
        private readonly ColorRepository $colors,
        private readonly HandleGeneratorFactory $handleGeneratorFactory,
        private readonly TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreateColorCommand $command): void
    {
        $this->transactionManager->execute(function () use ($command): Color {
            $color = Color::create(
                $command->name,
                new UserId($command->ownedBy),
                $this->handleGeneratorFactory->create(
                    fn (string $h) => null !== $this->colors->findByHandle(new ColorHandle($h))
                ),
            );

            $this->colors->save($color);

            return $color;
        });
    }
}
