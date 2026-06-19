<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\Color\ReadModel\ColorListItemView;
use App\ColorLab\Application\Color\ReadModel\ColorReadRepository;
use App\ColorLab\Application\Color\ReadModel\ColorView;
use App\ColorLab\Domain\Model\Color;
use App\ColorLab\Domain\Model\ColorHandle;
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

    /** @return list<ColorListItemView> */
    #[\Override]
    public function list(): array
    {
        return array_map(
            static fn (Color $color): ColorListItemView => new ColorListItemView(
                (string) $color->handle,
                $color->name,
            ),
            $this->findAll(),
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
