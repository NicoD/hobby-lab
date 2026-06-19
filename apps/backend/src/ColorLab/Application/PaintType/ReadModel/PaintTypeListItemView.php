<?php

declare(strict_types=1);

namespace App\ColorLab\Application\PaintType\ReadModel;

final readonly class PaintTypeListItemView
{
    public function __construct(
        public string $handle,
        public string $name,
    ) {
    }
}
