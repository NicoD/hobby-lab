<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Color\Command\CreateColor;

use App\ColorLab\Catalog\Domain\Color\Color;
use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\ColorLab\Catalog\Domain\Color\ColorRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateColorCommandHandler
{
    public function __construct(
        private ColorRepository $colors,
        private HandleGeneratorFactory $handleGeneratorFactory,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreateColorCommand $command): ColorHandle
    {
        $color = null;

        $this->transactionManager->execute(function () use ($command, &$color): Color {
            $color = Color::create(
                $command->name,
                new UserId($command->ownedBy),
                $this->handleGeneratorFactory->create(
                    fn (string $h): bool => $this->colors->findByHandle(new ColorHandle($h)) instanceof Color
                ),
            );

            $this->colors->save($color);

            return $color;
        });

        \assert($color instanceof Color);

        return $color->handle;
    }
}
