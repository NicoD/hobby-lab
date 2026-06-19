<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\Brand\ReadModel\BrandListItemView;
use App\ColorLab\Application\Brand\ReadModel\BrandReadRepository;
use App\ColorLab\Application\Brand\ReadModel\BrandView;
use App\ColorLab\Domain\Model\Brand;
use App\ColorLab\Domain\Model\BrandHandle;
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

    /** @return list<BrandListItemView> */
    public function list(): array
    {
        return array_map(
            static fn (Brand $brand): BrandListItemView => new BrandListItemView(
                (string) $brand->handle,
                $brand->name,
                array_map(
                    static fn (\App\ColorLab\Domain\Model\Range $range): array => ['handle' => (string) $range->handle, 'name' => $range->name],
                    $brand->ranges
                )
            ),
            $this->findAll(),
        );
    }

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
                static fn (\App\ColorLab\Domain\Model\Range $range): array => ['handle' => (string) $range->handle, 'name' => $range->name],
                $brand->ranges
            ));
    }
}
