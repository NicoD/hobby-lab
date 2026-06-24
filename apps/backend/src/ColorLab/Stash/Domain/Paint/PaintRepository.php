<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Domain\Paint;

interface PaintRepository
{
    public function save(Paint $paint): void;

    public function findById(PaintId $id): ?Paint;
}
