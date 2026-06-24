<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Paint;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
use App\ColorLab\Catalog\Domain\Brand\Range\RangeHandle;
use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\ColorLab\Catalog\Domain\Paint\Event\PaintCreatedEvent;
use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEventTrait;
use App\Shared\Domain\Model\AggregateRoot;
use App\Shared\Domain\Service\HandleGenerator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'catalog_paints')]
class Paint implements AggregateRoot
{
    use DomainEventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'catalog_paint_handle')]
        public private(set) PaintHandle $handle,
        #[ORM\Column(type: 'string', length: 255)]
        public private(set) string $name,
        #[ORM\Column(name: 'brand_handle', type: 'catalog_brand_handle', nullable: true)]
        public private(set) ?BrandHandle $brand,
        #[ORM\Column(name: 'range_handle', type: 'catalog_range_handle', nullable: true)]
        public private(set) ?RangeHandle $range,
        #[ORM\Column(name: 'paint_type_handle', type: 'catalog_paint_type_handle', nullable: true)]
        public private(set) ?PaintTypeHandle $paintType,
        #[ORM\Column(name: 'color_handle', type: 'catalog_color_handle', nullable: true)]
        public private(set) ?ColorHandle $color,
        #[ORM\Column(name: 'user_id', type: 'user_id')]
        public private(set) UserId $userId,
        #[ORM\Column(type: 'datetime_immutable')]
        public private(set) \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
    }

    public static function create(
        string $name,
        ?BrandHandle $brand,
        ?RangeHandle $range,
        ?PaintTypeHandle $paintType,
        ?ColorHandle $color,
        UserId $userId,
        HandleGenerator $handleGenerator,
    ): self {
        $paint = new self(
            new PaintHandle($handleGenerator->generate($name, str_cast($brand))),
            $name,
            $brand,
            $range,
            $paintType,
            $color,
            $userId,
        );

        $paint->raiseDomainEvent(new PaintCreatedEvent(
            $paint->handle,
            $paint->name,
            $brand,
            $range,
            $paintType,
            $color,
            $userId,
        ));

        return $paint;
    }
}
