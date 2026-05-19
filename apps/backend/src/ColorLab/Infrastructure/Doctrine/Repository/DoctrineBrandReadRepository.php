<?php

declare(strict_types=1);

namespace App\ColorLab\Infrastructure\Doctrine\Repository;

use App\ColorLab\Application\Brand\ReadModel\BrandListItemView;
use App\ColorLab\Application\Brand\ReadModel\BrandReadRepository;
use App\ColorLab\Application\Brand\ReadModel\BrandView;
use App\ColorLab\Domain\Brand;
use App\ColorLab\Domain\ValueObject\BrandHandle;
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

    /** @return BrandListItemView[] */
    public function list(): array
    {
        return array_map(
            static fn(Brand $brand) => new BrandListItemView($brand->handle->handle, $brand->name),
            $this->findAll(),
        );
    }

    public function findByHandle(string $handle): ?BrandView
    {
        $brand = $this->find(BrandHandle::fromString($handle));

        if (null === $brand) {
            return null;
        }

        return new BrandView($brand->handle->handle, $brand->name);
    }
}
