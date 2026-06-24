<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceCriteria;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceListItemView;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceReadRepository;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceView;
use App\ColorLab\Domain\Model\PaintReference;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\Shared\Application\Query\PaginatedResult;
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

    /**
     * @return PaginatedResult<PaintReferenceListItemView>
     */
    #[\Override]
    public function list(PaintReferenceCriteria $criteria): PaginatedResult
    {
        $conn = $this->getEntityManager()->getConnection();

        $where = 'pr.owned_by = :ownedBy';
        $params = ['ownedBy' => $criteria->ownedBy];

        if (null !== $criteria->search && '' !== $criteria->search) {
            $where .= ' AND LOWER(pr.name) LIKE :search';
            $params['search'] = '%'.addcslashes(strtolower($criteria->search), '%_\\').'%';
        }

        $total = (int) $conn->executeQuery(
            "SELECT COUNT(*) FROM paint_references pr WHERE {$where}",
            $params,
        )->fetchOne();

        $sortColumn = 'createdAt' === $criteria->sort->field ? 'pr.created_at' : 'pr.name';
        $sortDir = $criteria->sort->isAsc ? 'ASC' : 'DESC';

        $sql = <<<SQL
            SELECT
                pr.handle,
                pr.name,
                pr.created_at,
                pr.brand_handle,
                b.name AS brand_name,
                pr.range_handle,
                (
                    SELECT elem->>'name'
                    FROM json_array_elements(COALESCE(b.ranges, '[]'::json)) AS elem
                    WHERE elem->>'handle' = pr.range_handle
                    LIMIT 1
                ) AS range_name,
                pr.paint_type_handle,
                pt.name AS paint_type_name,
                pr.color_handle,
                c.name AS color_name
            FROM paint_references pr
            LEFT JOIN brands b ON b.handle = pr.brand_handle
            LEFT JOIN paint_types pt ON pt.handle = pr.paint_type_handle
            LEFT JOIN colors c ON c.handle = pr.color_handle
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
            array_map(static fn (array $row): PaintReferenceListItemView => new PaintReferenceListItemView(
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
    public function findByHandle(string $handle): ?PaintReferenceView
    {
        $ref = $this->find(new PaintReferenceHandle($handle));

        if (null === $ref) {
            return null;
        }

        return new PaintReferenceView(
            (string) $ref->handle,
            $ref->name,
            null !== $ref->brand ? (string) $ref->brand : null,
            null !== $ref->range ? (string) $ref->range : null,
            null !== $ref->paintType ? (string) $ref->paintType : null,
            null !== $ref->color ? (string) $ref->color : null,
        );
    }
}
