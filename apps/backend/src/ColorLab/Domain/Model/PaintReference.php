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
        #[ORM\Column(type: 'paint_reference_id')]
        public private(set) PaintReferenceId $id,
        #[ORM\Column(type: 'paint_reference_handle', unique: true)]
        public private(set) PaintReferenceHandle $handle,
        #[ORM\Column(type: 'string', length: 255)]
        public private(set) string $name,
        #[ORM\Column(type: 'brand_handle')]
        public private(set) BrandHandle $brandHandle,
        #[ORM\Column(type: 'range_handle')]
        public private(set) RangeHandle $rangeHandle,
        #[ORM\Column(type: 'paint_type_handle')]
        public private(set) PaintTypeHandle $paintTypeHandle,
        #[ORM\Column(type: 'color_handle', nullable: true)]
        public private(set) ?ColorHandle $colorHandle,
        #[ORM\Column(type: 'user_id')]
        public private(set) UserId $ownedBy
    )
    {
    }

    public static function create(
        string $name,
        BrandHandle $brandHandle,
        RangeHandle $rangeHandle,
        PaintTypeHandle $paintTypeHandle,
        ?ColorHandle $colorHandle,
        UserId $ownedBy,
        HandleGenerator $handleGenerator,
    ): self {
        $ref = new self(
            PaintReferenceId::create(),
            new PaintReferenceHandle($handleGenerator->generate("{$brandHandle} {$name}")),
            $name,
            $brandHandle,
            $rangeHandle,
            $paintTypeHandle,
            $colorHandle,
            $ownedBy,
        );

        $ref->raiseDomainEvent(new PaintReferenceCreatedEvent(
            $ref->id,
            $ref->handle,
            $name,
            $brandHandle,
            $rangeHandle,
            $paintTypeHandle,
            $colorHandle,
            $ownedBy,
        ));

        return $ref;
    }
}
