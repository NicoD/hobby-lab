<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\Command\CreatePaintType;

final readonly class CreatePaintTypeCommand
{
    public function __construct(
        public string $name,
        public string $ownedBy,
    ) {
    }
}
