<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Command\CreatePaintReference;

final readonly class CreatePaintReferenceCommand
{
    public function __construct(
        public string $name,
        public ?string $brandHandle,
        public ?string $rangeHandle,
        public ?string $paintTypeHandle,
        public ?string $colorHandle,
        public string $ownedBy,
    ) {
    }
}
