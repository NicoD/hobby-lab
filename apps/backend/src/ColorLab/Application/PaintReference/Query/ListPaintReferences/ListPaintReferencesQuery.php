<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\Query\ListPaintReferences;

final readonly class ListPaintReferencesQuery
{
    public function __construct(
        public string $ownedBy,
        public ?string $brandHandle = null,
        public ?string $rangeHandle = null,
        public ?string $paintTypeHandle = null,
        public ?string $colorHandle = null,
    ) {
    }
}
