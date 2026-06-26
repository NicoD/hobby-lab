<?php

declare(strict_types=1);

namespace Outbox\Publisher;

interface IntegrationEventResolverInterface
{
    /**
     * @param array<string, mixed> $domainPayload
     *
     * @return array<string, mixed>|null null if this domain type has no integration event
     */
    public function resolve(string $domainType, array $domainPayload): ?array;
}
