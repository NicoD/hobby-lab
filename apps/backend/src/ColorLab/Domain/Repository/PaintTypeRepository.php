<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Model\PaintType;
use App\ColorLab\Domain\Model\PaintTypeHandle;

interface PaintTypeRepository
{
    public function save(PaintType $paintType): void;

    public function findByHandle(PaintTypeHandle $handle): ?PaintType;
}
