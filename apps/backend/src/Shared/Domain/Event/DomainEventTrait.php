<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

trait DomainEventTrait
{
    /** @var list<DomainEvent> */
    private array $events = [];

    /** @return \Iterator<DomainEvent> */
    #[\Override]
    public function pullDomainEvents(): \Iterator
    {
        while (null !== $event = array_shift($this->events)) {
            yield $event;
        }
    }

    private function raiseDomainEvent(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
}
