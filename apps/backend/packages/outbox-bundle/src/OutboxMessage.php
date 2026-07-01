<?php

declare(strict_types=1);

namespace Outbox;

use Symfony\Component\Uid\Uuid;

final readonly class OutboxMessage
{
    public Uuid $id;

    /**
     * @param array<string, mixed> $domainPayload
     */
    public function __construct(
        string $id,
        public string $domainType,
        public array $domainPayload,
        public \DateTimeImmutable $occurredAt,
        public string $correlationId,
    ) {
        $this->id = Uuid::fromString($id);
    }
}
