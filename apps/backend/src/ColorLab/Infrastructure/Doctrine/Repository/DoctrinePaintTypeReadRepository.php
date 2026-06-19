<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\PaintType\ReadModel\PaintTypeListItemView;
use App\ColorLab\Application\PaintType\ReadModel\PaintTypeReadRepository;
use App\ColorLab\Application\PaintType\ReadModel\PaintTypeView;
use App\ColorLab\Domain\Model\PaintType;
use App\ColorLab\Domain\Model\PaintTypeHandle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaintType>
 */
final class DoctrinePaintTypeReadRepository extends ServiceEntityRepository implements PaintTypeReadRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaintType::class);
    }

    /** @return list<PaintTypeListItemView> */
    #[\Override]
    public function list(): array
    {
        return array_map(
            static fn (PaintType $pt) => new PaintTypeListItemView(
                (string) $pt->handle,
                $pt->name,
            ),
            $this->findAll(),
        );
    }

    #[\Override]
    public function findByHandle(string $handle): ?PaintTypeView
    {
        $pt = $this->find(new PaintTypeHandle($handle));

        if (null === $pt) {
            return null;
        }

        return new PaintTypeView((string) $pt->handle, $pt->name);
    }
}
