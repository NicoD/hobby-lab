<?php

declare(strict_types=1);

namespace Outbox\Publisher;

final readonly class NullEventPublisher implements EventPublisher
{
    #[\Override]
    public function publish(IntegrationEvent $event): void
    {
    }
}
