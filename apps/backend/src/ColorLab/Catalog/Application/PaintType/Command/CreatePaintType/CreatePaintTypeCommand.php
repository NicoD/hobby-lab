<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\PaintType\Command\CreatePaintType;

use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\Shared\Application\Bus\Command;

/** @implements Command<PaintTypeHandle> */
final readonly class CreatePaintTypeCommand implements Command
{
    public function __construct(
        public string $name,
        public string $ownedBy,
    ) {
    }
}
