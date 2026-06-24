<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Paint;

interface PaintRepository
{
    public function save(Paint $paint): void;

    public function findByHandle(PaintHandle $handle): ?Paint;
}
