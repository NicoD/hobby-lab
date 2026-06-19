<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\Command\CreateColor;

final readonly class CreateColorCommand
{
    public function __construct(
        public string $name,
        public string $ownedBy,
    ) {
    }
}
