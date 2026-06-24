<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Application\PaintType\ReadModel;

final readonly class PaintTypeView
{
    public function __construct(
        public string $handle,
        public string $name,
    ) {
    }
}
