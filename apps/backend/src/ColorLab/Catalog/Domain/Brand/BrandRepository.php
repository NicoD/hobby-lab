<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Brand;

interface BrandRepository
{
    public function save(Brand $brand): void;

    public function findByHandle(BrandHandle $handle): ?Brand;
}
