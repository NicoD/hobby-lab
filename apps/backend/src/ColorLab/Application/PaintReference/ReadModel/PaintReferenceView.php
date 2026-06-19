<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintReference\ReadModel;

final readonly class PaintReferenceView
{
    public function __construct(
        public string $id,
        public string $handle,
        public string $name,
        public string $brandHandle,
        public string $rangeHandle,
        public string $paintTypeHandle,
        public ?string $colorHandle,
    ) {
    }
}
