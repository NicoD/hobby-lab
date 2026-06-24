<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Color\Command\CreateColor;

use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\Shared\Application\Bus\Command;

/** @implements Command<ColorHandle> */
final readonly class CreateColorCommand implements Command
{
    public function __construct(
        public string $name,
        public string $ownedBy,
    ) {
    }
}
