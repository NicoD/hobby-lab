<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Paint\Command\CreatePaint;

use App\ColorLab\Domain\Model\PaintId;
use App\Shared\Application\Bus\Command;

/** @implements Command<PaintId> */
final readonly class CreatePaintCommand implements Command
{
    public function __construct(
        public string $paintReferenceHandle,
        public string $ownedBy,
        public ?string $purchasedAt,
    ) {
    }
}
