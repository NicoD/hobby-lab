<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Outbox;

use App\Shared\Application\Service\Outbox;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Correlation\CorrelationContext;
use Outbox\OutboxMessage;
use Outbox\OutboxNotifier;
use Outbox\OutboxRecorder;
use Symfony\Component\Uid\Uuid;

final readonly class OutboxAdapter implements Outbox
{
    public function __construct(
        private OutboxRecorder $outboxRecorder,
        private OutboxNotifier $outboxNotifier,
        private CorrelationContext $correlationContext,
    ) {
    }

    #[\Override]
    public function record(DomainEvent ...$events): void
    {
        $correlationId = $this->correlationContext->get() ?? Uuid::v4()->toRfc4122();

        $this->outboxRecorder->record(
            ...array_map(
                static fn (DomainEvent $event): OutboxMessage => new OutboxMessage(
                    (string) $event->eventId,
                    $event->type,
                    $event->payload,
                    $event->occurredAt,
                    $correlationId,
                ),
                $events,
            ),
        );
    }

    #[\Override]
    public function notify(): void
    {
        $this->outboxNotifier->notify();
    }
}
