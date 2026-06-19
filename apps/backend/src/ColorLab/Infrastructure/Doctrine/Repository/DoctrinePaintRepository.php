<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Domain\Model\Paint;
use App\ColorLab\Domain\Model\PaintId;
use App\ColorLab\Domain\Repository\PaintRepository;
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
    public function findById(PaintId $id): ?Paint
    {
        return $this->find($id);
    }
}
