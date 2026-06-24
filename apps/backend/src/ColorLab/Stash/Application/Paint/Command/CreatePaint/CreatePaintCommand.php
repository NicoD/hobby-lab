<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Application\Paint\Command\CreatePaint;

use App\ColorLab\Stash\Domain\Paint\PaintId;
use App\Shared\Application\Bus\Command;

/** @implements Command<PaintId> */
final readonly class CreatePaintCommand implements Command
{
    public function __construct(
        public string $paintHandle,
        public string $ownedBy,
        public ?string $purchasedAt,
    ) {
    }
}
