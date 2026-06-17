<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Repository;

use App\ColorLab\Domain\Model\Brand;
use App\ColorLab\Domain\Model\BrandHandle;
use App\Shared\Domain\Service\HandleExistenceChecker;

interface BrandRepository extends HandleExistenceChecker
{
    public function save(Brand $brand): void;

    public function findByHandle(BrandHandle $handle): ?Brand;

    public function handleExists(string $handle): bool;
}
