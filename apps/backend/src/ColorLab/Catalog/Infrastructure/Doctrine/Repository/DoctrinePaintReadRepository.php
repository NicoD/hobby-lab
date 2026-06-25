<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Infrastructure\Doctrine\Repository;

use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintCriteria;
use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintListItemView;
use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintReadRepository;
use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintView;
use App\ColorLab\Catalog\Domain\Paint\Paint;
use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\Shared\Application\Query\PaginatedResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paint>
 */
final class DoctrinePaintReadRepository extends ServiceEntityRepository implements PaintReadRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paint::class);
    }

    /** @return PaginatedResult<PaintListItemView> */
    #[\Override]
    public function list(PaintCriteria $criteria): PaginatedResult
    {
        $conn = $this->getEntityManager()->getConnection();

        $where = 'p.user_id = :ownedBy';
        $params = ['ownedBy' => $criteria->ownedBy];

        if (null !== $criteria->search && '' !== $criteria->search) {
            $where .= ' AND LOWER(p.name) LIKE :search';
            $params['search'] = '%'.addcslashes(strtolower($criteria->search), '%_\\').'%';
        }

        $count = $conn->executeQuery(
            "SELECT COUNT(*) FROM catalog_paints p WHERE {$where}",
            $params,
        )->fetchOne();

        $total = is_numeric($count) ? (int) $count : 0;

        $sortColumn = 'createdAt' === $criteria->sort->field ? 'p.created_at' : 'p.name';
        $sortDir = $criteria->sort->isAsc ? 'ASC' : 'DESC';

        $sql = <<<SQL
            SELECT
                p.handle,
                p.name,
                p.created_at,
                p.brand_handle,
                b.name AS brand_name,
                p.range_handle,
                (
                    SELECT elem->>'name'
                    FROM json_array_elements(COALESCE(b.ranges, '[]'::json)) AS elem
                    WHERE elem->>'handle' = p.range_handle
                    LIMIT 1
                ) AS range_name,
                p.paint_type_handle,
                pt.name AS paint_type_name,
                p.color_handle,
                c.name AS color_name
            FROM catalog_paints p
            LEFT JOIN catalog_brands b ON b.handle = p.brand_handle
            LEFT JOIN catalog_paint_types pt ON pt.handle = p.paint_type_handle
            LEFT JOIN catalog_colors c ON c.handle = p.color_handle
            WHERE {$where}
            ORDER BY {$sortColumn} {$sortDir}
            LIMIT :limit OFFSET :offset
            SQL;

        /** @var list<array{handle: string, name: string, created_at: string, brand_handle: string|null, brand_name: string|null, range_handle: string|null, range_name: string|null, paint_type_handle: string|null, paint_type_name: string|null, color_handle: string|null, color_name: string|null}> $rows */
        $rows = $conn->executeQuery(
            $sql,
            [...$params, 'limit' => $criteria->pagination->limit, 'offset' => $criteria->pagination->offset],
        )->fetchAllAssociative();

        return new PaginatedResult(
            array_map(static fn (array $row): PaintListItemView => new PaintListItemView(
                $row['handle'],
                $row['name'],
                new \DateTimeImmutable($row['created_at'])->format(\DateTimeInterface::ATOM),
                $row['brand_handle'],
                $row['brand_name'],
                $row['range_handle'],
                $row['range_name'],
                $row['paint_type_handle'],
                $row['paint_type_name'],
                $row['color_handle'],
                $row['color_name'],
            ), $rows),
            $total,
            $criteria->pagination->page,
            $criteria->pagination->limit,
        );
    }

    #[\Override]
    public function findByHandle(string $handle): ?PaintView
    {
        $paint = $this->find(new PaintHandle($handle));

        if (null === $paint) {
            return null;
        }

        return new PaintView(
            (string) $paint->handle,
            $paint->name,
            null !== $paint->brand ? (string) $paint->brand : null,
            null !== $paint->range ? (string) $paint->range : null,
            null !== $paint->paintType ? (string) $paint->paintType : null,
            null !== $paint->color ? (string) $paint->color : null,
        );
    }
}
