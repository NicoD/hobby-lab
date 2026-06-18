<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Shared\Domain\Model\AggregateRootId;

class DomainEvent
{
    public readonly DomainEventId $eventId;
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct(public readonly AggregateRootId $aggregateId)
    {
        $this->eventId = new DomainEventId();
        $this->occurredAt = new \DateTimeImmutable();
    }
}
