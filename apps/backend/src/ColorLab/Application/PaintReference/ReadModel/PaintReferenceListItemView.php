<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\ReadModel;

final readonly class PaintReferenceListItemView
{
    public function __construct(
        public string $handle,
        public string $name,
        public ?string $brandHandle,
        public ?string $rangeHandle,
        public ?string $paintTypeHandle,
        public ?string $colorHandle,
    ) {
    }
}
