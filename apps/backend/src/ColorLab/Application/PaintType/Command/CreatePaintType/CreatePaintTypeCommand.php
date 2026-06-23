<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Command\CreatePaintType;

use App\ColorLab\Domain\Model\PaintTypeHandle;
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
