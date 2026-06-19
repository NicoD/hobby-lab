<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\ReadModel;

final readonly class PaintReferenceCriteria
{
    public function __construct(
        public string $ownedBy,
        public ?string $brand = null,
        public ?string $range = null,
        public ?string $paintType = null,
        public ?string $color = null,
    ) {
    }
}
