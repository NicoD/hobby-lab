<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceCriteria;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceView;
use App\ColorLab\Domain\Model\BrandHandle;
use App\ColorLab\Domain\Model\ColorHandle;
use App\ColorLab\Domain\Model\PaintReference;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\ColorLab\Domain\Model\PaintTypeHandle;
use App\ColorLab\Domain\Model\RangeHandle;
use App\Shared\Domain\Model\UserId;
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
    public function list(PaintReferenceCriteria $criteria): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.ownedBy = :ownedBy')
            ->setParameter('ownedBy', new UserId($criteria->ownedBy), 'user_id');

        if (null !== $criteria->brand) {
            $qb->andWhere('p.brand = :brand')
                ->setParameter('brand', new BrandHandle($criteria->brand), 'brand_handle');
        }

        if (null !== $criteria->range) {
            $qb->andWhere('p.range = :range')
                ->setParameter('range', new RangeHandle($criteria->range), 'range_handle');
        }

        if (null !== $criteria->paintType) {
            $qb->andWhere('p.paintType = :paintType')
                ->setParameter('paintType', new PaintTypeHandle($criteria->paintType), 'paint_type_handle');
        }

        if (null !== $criteria->color) {
            $qb->andWhere('p.color = :color')
                ->setParameter('color', new ColorHandle($criteria->color), 'color_handle');
        }

        /** @var list<PaintReference> $results */
        $results = $qb->getQuery()->getResult();

        return array_map(
            static fn (PaintReference $ref): PaintReferenceListItemView => new PaintReferenceListItemView(
                (string) $ref->id,
                (string) $ref->handle,
                $ref->name,
                (string) $ref->brand,
                (string) $ref->range,
                (string) $ref->paintType,
                $ref->color instanceof \App\ColorLab\Domain\Model\ColorHandle ? (string) $ref->color : null,
            ),
            $results,
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
            (string) $ref->brand,
            (string) $ref->range,
            (string) $ref->paintType,
            null !== $ref->color ? (string) $ref->color : null,
        );
    }
}
