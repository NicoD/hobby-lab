<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceView;
use App\ColorLab\Domain\Model\PaintReference;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaintReference>
 */
final class DoctrinePaintReferenceReadRepository extends ServiceEntityRepository implements PaintReferenceReadRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaintReference::class);
    }

    /** @return list<PaintReferenceListItemView> */
    #[\Override]
    public function list(): array
    {
        return array_map(
            static fn (PaintReference $ref): \App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView => new PaintReferenceListItemView(
                (string) $ref->id,
                (string) $ref->handle,
                $ref->name,
                (string) $ref->brandHandle,
                (string) $ref->rangeHandle,
                (string) $ref->paintTypeHandle,
                $ref->colorHandle instanceof \App\ColorLab\Domain\Model\ColorHandle ? (string) $ref->colorHandle : null,
            ),
            $this->findAll(),
        );
    }

    #[\Override]
    public function findByHandle(string $handle): ?PaintReferenceView
    {
        $ref = $this->findOneBy(['handle' => new PaintReferenceHandle($handle)]);

        if (null === $ref) {
            return null;
        }

        return new PaintReferenceView(
            (string) $ref->id,
            (string) $ref->handle,
            $ref->name,
            (string) $ref->brandHandle,
            (string) $ref->rangeHandle,
            (string) $ref->paintTypeHandle,
            null !== $ref->colorHandle ? (string) $ref->colorHandle : null,
        );
    }
}
