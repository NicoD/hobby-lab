<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Model\Color;
use App\ColorLab\Domain\Model\ColorHandle;

interface ColorRepository
{
    public function save(Color $color): void;

    public function findByHandle(ColorHandle $handle): ?Color;
}
