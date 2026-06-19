<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Color\ReadModel;

final readonly class ColorListItemView
{
    public function __construct(
        public string $handle,
        public string $name,
    ) {
    }
}
