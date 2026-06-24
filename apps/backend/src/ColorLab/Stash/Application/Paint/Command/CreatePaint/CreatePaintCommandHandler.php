<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Application\Paint\Command\CreatePaint;

use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Stash\Domain\Paint\Paint;
use App\ColorLab\Stash\Domain\Paint\PaintId;
use App\ColorLab\Stash\Domain\Paint\PaintRepository;
use App\Identity\UserId;
use App\Shared\Application\Service\TransactionManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreatePaintCommandHandler
{
    public function __construct(
        private PaintRepository $paints,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreatePaintCommand $command): PaintId
    {
        $paint = null;

        $this->transactionManager->execute(function () use ($command, &$paint): Paint {
            $purchasedAt = null !== $command->purchasedAt
                ? new \DateTimeImmutable($command->purchasedAt)
                : null;

            $paint = Paint::create(
                new PaintHandle($command->paintHandle),
                new UserId($command->ownedBy),
                $purchasedAt,
            );

            $this->paints->save($paint);

            return $paint;
        });

        \assert($paint instanceof Paint);

        return $paint->id;
    }
}
