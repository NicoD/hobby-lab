<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.outbox.integration_event_mapper')]
interface IntegrationEventMapper
{
    public function supports(string $domainType): bool;

    /**
     * @param array<string, mixed> $domainPayload
     *
     * @return array<string, mixed>
     */
    public function map(array $domainPayload): array;
}
