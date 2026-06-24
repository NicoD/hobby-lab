<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\Paint\ReadModel;

final readonly class PaintView
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
