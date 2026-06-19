<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Command\CreatePaint;

use App\ColorLab\Domain\Model\Paint;
use App\ColorLab\Domain\Model\PaintReferenceId;
use App\ColorLab\Domain\Repository\PaintRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;

final readonly class CreatePaintCommandHandler
{
    public function __construct(
        private PaintRepository $paints,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreatePaintCommand $command): void
    {
        $this->transactionManager->execute(function () use ($command): Paint {
            $purchasedAt = null !== $command->purchasedAt
                ? new \DateTimeImmutable($command->purchasedAt)
                : null;

            $paint = Paint::create(
                new PaintReferenceId($command->paintReferenceId),
                new UserId($command->ownedBy),
                $purchasedAt,
            );

            $this->paints->save($paint);

            return $paint;
        });
    }
}
