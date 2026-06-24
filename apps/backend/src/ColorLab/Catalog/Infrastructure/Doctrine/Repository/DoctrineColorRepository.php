<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Repository;

use App\ColorLab\Catalog\Domain\Color\Color;
use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\ColorLab\Catalog\Domain\Color\ColorRepository;
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
