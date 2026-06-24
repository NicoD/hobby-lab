<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Infrastructure\Doctrine\Repository;

use App\ColorLab\Stash\Domain\Paint\Paint;
use App\ColorLab\Stash\Domain\Paint\PaintId;
use App\ColorLab\Stash\Domain\Paint\PaintRepository;
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
