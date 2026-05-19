<?php

declare(strict_types=1);

namespace App\ColorLab\Application\Brand\ReadModel;

final readonly class BrandView
{
    public function __construct(
        public string $handle,
        public string $name,
    ) {}
}
