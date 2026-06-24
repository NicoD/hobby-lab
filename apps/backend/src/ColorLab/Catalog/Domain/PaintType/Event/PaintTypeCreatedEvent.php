<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\PaintType\Event;

use App\ColorLab\Catalog\Domain\PaintType\PaintTypeHandle;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\UserId;

final class PaintTypeCreatedEvent extends DomainEvent
{
    public function __construct(
        PaintTypeHandle $aggregateId,
        public readonly string $name,
        public readonly UserId $ownedBy,
    ) {
        parent::__construct($aggregateId);
    }
}
