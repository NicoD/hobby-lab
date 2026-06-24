<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Domain\Paint;

use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Stash\Domain\Paint\Event\PaintCreatedEvent;
use App\Identity\UserId;
use App\Shared\Domain\Event\DomainEventTrait;
use App\Shared\Domain\Model\AggregateRoot;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stash_paints')]
class Paint implements AggregateRoot
{
    use DomainEventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'stash_paint_id')]
        public private(set) PaintId $id,
        #[ORM\Column(name: 'paint_handle', type: 'catalog_paint_handle')]
        public private(set) PaintHandle $paintHandle,
        #[ORM\Column(type: 'user_id')]
        public private(set) UserId $ownedBy,
        #[ORM\Column(type: 'date_immutable', nullable: true)]
        public private(set) ?\DateTimeImmutable $purchasedAt,
    ) {
    }

    public static function create(
        PaintHandle $paintHandle,
        UserId $ownedBy,
        ?\DateTimeImmutable $purchasedAt,
    ): self {
        $paint = new self(
            PaintId::create(),
            $paintHandle,
            $ownedBy,
            $purchasedAt,
        );

        $paint->raiseDomainEvent(new PaintCreatedEvent(
            $paint->id,
            $paintHandle,
            $ownedBy,
            $purchasedAt,
        ));

        return $paint;
    }
}
