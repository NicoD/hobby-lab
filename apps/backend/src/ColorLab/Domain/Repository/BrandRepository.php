<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Model\Brand;
use App\ColorLab\Domain\Model\BrandHandle;

interface BrandRepository
{
    public function save(Brand $brand): void;

    public function findByHandle(BrandHandle $handle): ?Brand;
}
