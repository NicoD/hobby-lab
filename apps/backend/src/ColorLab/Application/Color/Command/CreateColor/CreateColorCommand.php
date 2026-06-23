<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Command\CreateColor;

use App\ColorLab\Domain\Model\ColorHandle;
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
