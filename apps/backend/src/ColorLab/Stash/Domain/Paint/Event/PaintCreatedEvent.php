<?php

declare(strict_types=1);

namespace App\ColorLab\Stash\Domain\Paint\Event;

use App\ColorLab\Catalog\Domain\Paint\PaintHandle;
use App\ColorLab\Stash\Domain\Paint\PaintId;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\UserId;

final class PaintCreatedEvent extends DomainEvent
{
    public function __construct(
        PaintId $aggregateId,
        public readonly PaintHandle $paintHandle,
        public readonly UserId $ownedBy,
        public readonly ?\DateTimeImmutable $purchasedAt,
    ) {
        parent::__construct($aggregateId);
    }
}
