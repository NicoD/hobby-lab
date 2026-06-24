<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Repository;

use App\ColorLab\Catalog\Domain\Paint\Paint;
use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Catalog\Domain\Paint\PaintRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paint>
 */
final class DoctrinePaintRepository extends ServiceEntityRepository implements PaintRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paint::class);
    }

    #[\Override]
    public function save(Paint $paint): void
    {
        $this->getEntityManager()->persist($paint);
    }

    #[\Override]
    public function findByHandle(PaintHandle $handle): ?Paint
    {
        return $this->find($handle);
    }
}
