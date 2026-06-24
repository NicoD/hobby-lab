<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Color\Event;

use App\ColorLab\Catalog\Domain\Color\ColorHandle;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\UserId;

final class ColorCreatedEvent extends DomainEvent
{
    public function __construct(
        ColorHandle $aggregateId,
        public readonly string $name,
        public readonly UserId $ownedBy,
    ) {
        parent::__construct($aggregateId);
    }
}
