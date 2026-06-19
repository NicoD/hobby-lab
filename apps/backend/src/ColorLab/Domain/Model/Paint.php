<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Model;

use App\ColorLab\Domain\Event\PaintCreatedEvent;
use App\Shared\Domain\Event\DomainEventTrait;
use App\Shared\Domain\Model\AggregateRoot;
use App\Shared\Domain\Model\UserId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paints')]
class Paint implements AggregateRoot
{
    use DomainEventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'paint_id')]
        public private(set) PaintId $id,
        #[ORM\Column(name: 'paint_reference_id', type: 'paint_reference_id')]
        public private(set) PaintReferenceId $paintReference,
        #[ORM\Column(type: 'user_id')]
        public private(set) UserId $ownedBy,
        #[ORM\Column(type: 'date_immutable', nullable: true)]
        public private(set) ?\DateTimeImmutable $purchasedAt,
    ) {
    }

    public static function create(
        PaintReferenceId $paintReference,
        UserId $ownedBy,
        ?\DateTimeImmutable $purchasedAt,
    ): self {
        $paint = new self(
            PaintId::create(),
            $paintReference,
            $ownedBy,
            $purchasedAt,
        );

        $paint->raiseDomainEvent(new PaintCreatedEvent(
            $paint->id,
            $paintReference,
            $ownedBy,
            $purchasedAt,
        ));

        return $paint;
    }
}
