<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Domain\Model\Color;
use App\ColorLab\Domain\Model\ColorHandle;
use App\ColorLab\Domain\Repository\ColorRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Color>
 */
final class DoctrineColorRepository extends ServiceEntityRepository implements ColorRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Color::class);
    }

    #[\Override]
    public function save(Color $color): void
    {
        $this->getEntityManager()->persist($color);
    }

    #[\Override]
    public function findByHandle(ColorHandle $handle): ?Color
    {
        return $this->find($handle);
    }
}
