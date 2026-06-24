<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\Color\ReadModel\ColorCriteria;
use App\ColorLab\Application\Color\ReadModel\ColorListItemView;
use App\ColorLab\Application\Color\ReadModel\ColorReadRepository;
use App\ColorLab\Application\Color\ReadModel\ColorView;
use App\ColorLab\Domain\Model\Color;
use App\ColorLab\Domain\Model\ColorHandle;
use App\Shared\Application\Query\PaginatedResult;
use App\Shared\Domain\Model\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Color>
 */
final class DoctrineColorReadRepository extends ServiceEntityRepository implements ColorReadRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Color::class);
    }

    /**
     * @return PaginatedResult<ColorListItemView>
     */
    #[\Override]
    public function list(ColorCriteria $criteria): PaginatedResult
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.ownedBy = :ownedBy')
            ->setParameter('ownedBy', new UserId($criteria->ownedBy), 'user_id');

        if (null !== $criteria->search && '' !== $criteria->search) {
            $qb->andWhere('LOWER(c.name) LIKE :search')
                ->setParameter('search', '%'.addcslashes(strtolower($criteria->search), '%_\\').'%');
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(c.handle)')->getQuery()->getSingleScalarResult();

        $sortField = 'createdAt' === $criteria->sort->field ? 'c.createdAt' : 'c.name';

        $qb->orderBy($sortField, $criteria->sort->isAsc ? 'ASC' : 'DESC')
            ->setFirstResult($criteria->pagination->offset)
            ->setMaxResults($criteria->pagination->limit);

        /** @var list<Color> $colors */
        $colors = $qb->getQuery()->getResult();

        return new PaginatedResult(
            array_map(static fn (Color $color): ColorListItemView => new ColorListItemView(
                (string) $color->handle,
                $color->name,
                $color->createdAt->format(\DateTimeInterface::ATOM),
            ), $colors),
            $total,
            $criteria->pagination->page,
            $criteria->pagination->limit,
        );
    }

    #[\Override]
    public function findByHandle(string $handle): ?ColorView
    {
        $color = $this->find(new ColorHandle($handle));

        if (null === $color) {
            return null;
        }

        return new ColorView((string) $color->handle, $color->name);
    }
}
