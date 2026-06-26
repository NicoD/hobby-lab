<?php

declare(strict_types=1);

namespace Outbox\Publisher;

final readonly class IntegrationEvent
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $id,
        public string $type,
        public array $payload,
    ) {
    }
}
