<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\PaintType;

interface PaintTypeRepository
{
    public function save(PaintType $paintType): void;

    public function findByHandle(PaintTypeHandle $handle): ?PaintType;
}
