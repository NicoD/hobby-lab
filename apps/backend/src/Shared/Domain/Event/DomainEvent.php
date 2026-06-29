<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Shared\Domain\Model\AggregateRootId;

abstract class DomainEvent
{
    public readonly DomainEventId $eventId;
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct(public readonly AggregateRootId $aggregateId)
    {
        $this->eventId = new DomainEventId();
        $this->occurredAt = new \DateTimeImmutable();
    }

    abstract public string $type { get; }

    /** @var array<string, scalar|null> */
    abstract public array $payload { get; }
}
