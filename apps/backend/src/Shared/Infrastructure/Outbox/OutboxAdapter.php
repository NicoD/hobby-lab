<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Outbox;

use App\Shared\Application\Service\Outbox;
use App\Shared\Domain\Event\DomainEvent;
use Outbox\OutboxMessage;
use Outbox\OutboxNotifier;
use Outbox\OutboxRecorder;

final readonly class OutboxAdapter implements Outbox
{
    public function __construct(
        private OutboxRecorder $outboxRecorder,
        private OutboxNotifier $outboxNotifier)
    {
    }

    #[\Override]
    public function record(DomainEvent ...$events): void
    {
        $this->outboxRecorder->record(
            ...array_map(
                static fn (DomainEvent $event): OutboxMessage => new OutboxMessage(
                    (string) $event->eventId,
                    $event->type,
                    $event->payload,
                    $event->occurredAt,
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
