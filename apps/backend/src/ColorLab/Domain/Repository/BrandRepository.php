<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Brand;
use App\ColorLab\Domain\ValueObject\BrandHandle;

interface BrandRepository
{
    public function save(Brand $brand): void;

    public function findByHandle(BrandHandle $handle): ?Brand;
}
