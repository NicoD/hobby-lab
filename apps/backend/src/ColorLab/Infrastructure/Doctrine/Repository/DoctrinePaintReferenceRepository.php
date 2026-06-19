<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Domain\Model\PaintReference;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\ColorLab\Domain\Model\PaintReferenceId;
use App\ColorLab\Domain\Repository\PaintReferenceRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaintReference>
 */
final class DoctrinePaintReferenceRepository extends ServiceEntityRepository implements PaintReferenceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaintReference::class);
    }

    #[\Override]
    public function save(PaintReference $paintReference): void
    {
        $this->getEntityManager()->persist($paintReference);
    }

    #[\Override]
    public function findById(PaintReferenceId $id): ?PaintReference
    {
        return $this->find($id);
    }

    #[\Override]
    public function findByHandle(PaintReferenceHandle $handle): ?PaintReference
    {
        return $this->findOneBy(['handle' => $handle]);
    }
}
