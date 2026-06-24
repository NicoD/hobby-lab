<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Repository;

use App\ColorLab\Catalog\Domain\Brand\Brand;
use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\ColorLab\Catalog\Domain\Brand\BrandRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brand>
 */
final class DoctrineBrandRepository extends ServiceEntityRepository implements BrandRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    #[\Override]
    public function save(Brand $brand): void
    {
        $this->getEntityManager()->persist($brand);
    }

    #[\Override]
    public function findByHandle(BrandHandle $handle): ?Brand
    {
        return $this->find($handle);
    }
}
