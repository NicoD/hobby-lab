<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Model\Paint;
use App\ColorLab\Domain\Model\PaintId;

interface PaintRepository
{
    public function save(Paint $paint): void;

    public function findById(PaintId $id): ?Paint;
}
