<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\Paint\ReadModel\PaintListItemView;
use App\ColorLab\Application\Paint\ReadModel\PaintReadRepository;
use App\ColorLab\Application\Paint\ReadModel\PaintView;
use App\ColorLab\Domain\Model\Paint;
use App\ColorLab\Domain\Model\PaintId;
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

    /** @return list<PaintListItemView> */
    #[\Override]
    public function list(): array
    {
        return array_map(
            static fn (Paint $paint): \App\ColorLab\Application\Paint\ReadModel\PaintListItemView => new PaintListItemView(
                (string) $paint->id,
                (string) $paint->paintReferenceId,
                $paint->purchasedAt?->format('Y-m-d'),
            ),
            $this->findAll(),
        );
    }

    #[\Override]
    public function findById(string $id): ?PaintView
    {
        $paint = $this->find(new PaintId($id));

        if (null === $paint) {
            return null;
        }

        return new PaintView(
            (string) $paint->id,
            (string) $paint->paintReferenceId,
            $paint->purchasedAt?->format('Y-m-d'),
        );
    }
}
