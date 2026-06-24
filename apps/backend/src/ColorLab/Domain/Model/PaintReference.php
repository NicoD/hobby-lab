<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\ColorLab\Domain\Event\PaintReferenceCreatedEvent;
use App\Shared\Domain\Event\DomainEventTrait;
use App\Shared\Domain\Model\AggregateRoot;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Service\HandleGenerator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paint_references')]
class PaintReference implements AggregateRoot
{
    use DomainEventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'paint_reference_handle')]
        public private(set) PaintReferenceHandle $handle,
        #[ORM\Column(type: 'string', length: 255)]
        public private(set) string $name,
        #[ORM\Column(name: 'brand_handle', type: 'brand_handle', nullable: true)]
        public private(set) ?BrandHandle $brand,
        #[ORM\Column(name: 'range_handle', type: 'range_handle', nullable: true)]
        public private(set) ?RangeHandle $range,
        #[ORM\Column(name: 'paint_type_handle', type: 'paint_type_handle', nullable: true)]
        public private(set) ?PaintTypeHandle $paintType,
        #[ORM\Column(name: 'color_handle', type: 'color_handle', nullable: true)]
        public private(set) ?ColorHandle $color,
        #[ORM\Column(type: 'user_id')]
        public private(set) UserId $ownedBy,
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
        UserId $ownedBy,
        HandleGenerator $handleGenerator,
    ): self {
        $ref = new self(
            new PaintReferenceHandle($handleGenerator->generate($name, str_cast($brand))),
            $name,
            $brand,
            $range,
            $paintType,
            $color,
            $ownedBy,
        );

        $ref->raiseDomainEvent(new PaintReferenceCreatedEvent(
            $ref->handle,
            $ref->name,
            $brand,
            $range,
            $paintType,
            $color,
            $ownedBy,
        ));

        return $ref;
    }
}
