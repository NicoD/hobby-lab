<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event;

use App\Shared\Application\Service\DomainEventDispatcher;
use App\Shared\Domain\Event\DomainEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SymfonyDomainEventDispatcher implements DomainEventDispatcher
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    #[\Override]
    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
