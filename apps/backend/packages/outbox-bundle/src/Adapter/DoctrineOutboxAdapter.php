<?php

declare(strict_types=1);

namespace Outbox\Adapter;

use Outbox\OutboxMessage;
use Outbox\OutboxNotifier;
use Outbox\OutboxRecorder;
use Outbox\PostgresConnection;

final readonly class DoctrineOutboxAdapter implements OutboxRecorder, OutboxNotifier
{
    public function __construct(private PostgresConnection $connection)
    {
    }

    #[\Override]
    public function record(OutboxMessage ...$messages): void
    {
        foreach ($messages as $message) {
            $this->connection->insert('outbox_events', [
                'id' => $message->id->toRfc4122(),
                'domain_type' => $message->domainType,
                'domain_payload' => json_encode($message->domainPayload, \JSON_THROW_ON_ERROR),
                'occurred_at' => $message->occurredAt->format('Y-m-d H:i:s.u P'),
                'created_at' => new \DateTimeImmutable()->format('Y-m-d H:i:s.u P'),
                'correlation_id' => $message->correlationId,
            ]);
        }
    }

    #[\Override]
    public function notify(): void
    {
        $this->connection->executeStatement("SELECT pg_notify('outbox_new_event', '')");
    }
}
