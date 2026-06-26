<?php

declare(strict_types=1);

namespace Outbox\Publisher;

interface EventPublisher
{
    public function publish(IntegrationEvent $event): void;
}
