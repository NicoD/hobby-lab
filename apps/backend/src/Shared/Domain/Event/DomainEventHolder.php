<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

interface DomainEventHolder
{
    /** @return \Iterator<DomainEvent> */
    public function pullDomainEvents(): \Iterator;
}
