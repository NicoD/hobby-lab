<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Paint\Command\CreatePaint;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\ColorLab\Catalog\Domain\Paint\Paint;
use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Catalog\Domain\Paint\PaintRepository;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\ColorLab\Catalog\Domain\Range\RangeHandle;
use App\Shared\Application\Service\TransactionManager;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGeneratorFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreatePaintCommandHandler
{
    public function __construct(
        private PaintRepository $paints,
        private HandleGeneratorFactory $handleGeneratorFactory,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CreatePaintCommand $command): PaintHandle
    {
        $paint = null;

        $this->transactionManager->execute(function () use ($command, &$paint): Paint {
            $paint = Paint::create(
                $command->name,
                wrap($command->brandHandle, BrandHandle::class),
                wrap($command->rangeHandle, RangeHandle::class),
                wrap($command->paintTypeHandle, PaintTypeHandle::class),
                wrap($command->colorHandle, ColorHandle::class),
                new UserId($command->ownedBy),
                $this->handleGeneratorFactory->create(
                    fn (string $h): bool => $this->paints->findByHandle(new PaintHandle($h)) instanceof Paint
                ),
            );

            $this->paints->save($paint);

            return $paint;
        });

        \assert($paint instanceof Paint);

        return $paint->handle;
    }
}
