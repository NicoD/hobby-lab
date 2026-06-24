<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Repository;

use App\ColorLab\Catalog\Domain\PaintType\PaintType;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaintType>
 */
final class DoctrinePaintTypeRepository extends ServiceEntityRepository implements PaintTypeRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaintType::class);
    }

    #[\Override]
    public function save(PaintType $paintType): void
    {
        $this->getEntityManager()->persist($paintType);
    }

    #[\Override]
    public function findByHandle(PaintTypeHandle $handle): ?PaintType
    {
        return $this->find($handle);
    }
}
