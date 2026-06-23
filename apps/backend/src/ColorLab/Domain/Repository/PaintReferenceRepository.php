<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Model\PaintReference;
use App\ColorLab\Domain\Model\PaintReferenceHandle;

interface PaintReferenceRepository
{
    public function save(PaintReference $paintReference): void;

    public function findByHandle(PaintReferenceHandle $handle): ?PaintReference;
}
