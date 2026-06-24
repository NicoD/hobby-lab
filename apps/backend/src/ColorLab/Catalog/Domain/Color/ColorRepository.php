<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Color;

interface ColorRepository
{
    public function save(Color $color): void;

    public function findByHandle(ColorHandle $handle): ?Color;
}
