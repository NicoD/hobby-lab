<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Model\PaintReference;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\ColorLab\Domain\Model\PaintReferenceId;

interface PaintReferenceRepository
{
    public function save(PaintReference $paintReference): void;

    public function findById(PaintReferenceId $id): ?PaintReference;

    public function findByHandle(PaintReferenceHandle $handle): ?PaintReference;
}
