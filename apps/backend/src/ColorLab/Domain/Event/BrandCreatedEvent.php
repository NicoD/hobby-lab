<?php

declare(strict_types=1);

namespace App\ColorLab\Domain\Event;

use App\ColorLab\Domain\Model\BrandHandle;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\UserId;

final class BrandCreatedEvent extends DomainEvent
{
    public function __construct(
        BrandHandle $aggregateId,
        public readonly string $name,
        public readonly UserId $ownedBy,
    ) {
        parent::__construct($aggregateId);
    }
}
