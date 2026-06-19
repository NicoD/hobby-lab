<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event;

use App\Shared\Application\Service\DomainEventDispatcher;
use App\Shared\Domain\Event\DomainEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(DomainEventDispatcher::class)]
final readonly class LoggingDomainEventDispatcher implements DomainEventDispatcher
{
    public function __construct(
        private DomainEventDispatcher $inner,
        private LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->logger->info('Domain event dispatched', [
                'event' => $event::class,
                'aggregate_id' => (string) $event->aggregateId,
                'event_id' => (string) $event->eventId,
            ]);
        }

        $this->inner->dispatch(...$events);
    }
}
