<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Domain\Model\Brand;
use App\ColorLab\Domain\Model\BrandHandle;
use App\ColorLab\Domain\Repository\BrandRepository;
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

    public function save(Brand $brand): void
    {
        $this->getEntityManager()->persist($brand);
        $this->getEntityManager()->flush();
    }

    public function findByHandle(BrandHandle $handle): ?Brand
    {
        return $this->find($handle);
    }

    public function handleExists(string $handle): bool
    {
        return false;
    }
}
