<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\Domain\Brand\Event;

use App\ColorLab\Catalog\Domain\Brand\BrandHandle;
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
