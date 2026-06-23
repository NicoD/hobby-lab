<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Event;

use App\ColorLab\Domain\Model\PaintId;
use App\ColorLab\Domain\Model\PaintReferenceHandle;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\UserId;

final class PaintCreatedEvent extends DomainEvent
{
    public function __construct(
        PaintId $aggregateId,
        public readonly PaintReferenceHandle $paintReferenceHandle,
        public readonly UserId $ownedBy,
        public readonly ?\DateTimeImmutable $purchasedAt,
    ) {
        parent::__construct($aggregateId);
    }
}
