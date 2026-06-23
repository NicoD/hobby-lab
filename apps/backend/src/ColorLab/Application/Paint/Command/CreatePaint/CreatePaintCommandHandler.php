<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Command\CreatePaint;

use App\ColorLab\Domain\Model\Paint;
use App\ColorLab\Domain\Model\PaintId;
use App\ColorLab\Domain\Model\PaintReferenceId;
use App\ColorLab\Domain\Repository\PaintRepository;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
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
                new PaintReferenceId($command->paintReferenceId),
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
