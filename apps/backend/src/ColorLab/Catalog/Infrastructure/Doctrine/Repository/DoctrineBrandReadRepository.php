<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Repository;

use App\ColorLab\Catalog\Application\Brand\ReadModel\BrandCriteria;
use App\ColorLab\Catalog\Application\Brand\ReadModel\BrandListItemView;
use App\ColorLab\Catalog\Application\Brand\ReadModel\BrandReadRepository;
use App\ColorLab\Catalog\Application\Brand\ReadModel\BrandView;
use App\ColorLab\Catalog\Domain\Brand\Brand;
use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\ColorLab\Catalog\Domain\Range\Range;
use App\Shared\Application\Query\PaginatedResult;
use App\Shared\Domain\Model\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brand>
 */
final class DoctrineBrandReadRepository extends ServiceEntityRepository implements BrandReadRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    /** @return PaginatedResult<BrandListItemView> */
    #[\Override]
    public function list(BrandCriteria $criteria): PaginatedResult
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.ownedBy = :ownedBy')
            ->setParameter('ownedBy', new UserId($criteria->ownedBy), 'user_id');

        if (null !== $criteria->search && '' !== $criteria->search) {
            $qb->andWhere('LOWER(b.name) LIKE :search')
                ->setParameter('search', '%'.addcslashes(strtolower($criteria->search), '%_\\').'%');
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(b.handle)')->getQuery()->getSingleScalarResult();

        $sortField = 'createdAt' === $criteria->sort->field ? 'b.createdAt' : 'b.name';

        $qb->orderBy($sortField, $criteria->sort->isAsc ? 'ASC' : 'DESC')
            ->setFirstResult($criteria->pagination->offset)
            ->setMaxResults($criteria->pagination->limit);

        /** @var list<Brand> $brands */
        $brands = $qb->getQuery()->getResult();

        return new PaginatedResult(
            array_map(static fn (Brand $brand): BrandListItemView => new BrandListItemView(
                (string) $brand->handle,
                $brand->name,
                array_map(
                    static fn (Range $range): array => ['handle' => (string) $range->handle, 'name' => $range->name],
                    $brand->ranges,
                ),
                $brand->createdAt->format(\DateTimeInterface::ATOM),
            ), $brands),
            $total,
            $criteria->pagination->page,
            $criteria->pagination->limit,
        );
    }

    #[\Override]
    public function findByHandle(string $handle): ?BrandView
    {
        $brand = $this->find(new BrandHandle($handle));

        if (null === $brand) {
            return null;
        }

        return new BrandView(
            (string) $brand->handle,
            $brand->name,
            array_map(
                static fn (Range $range): array => ['handle' => (string) $range->handle, 'name' => $range->name],
                $brand->ranges,
            ),
        );
    }
}
