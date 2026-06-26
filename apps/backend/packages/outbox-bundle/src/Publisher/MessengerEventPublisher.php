<?php

declare(strict_types=1);

namespace Outbox\Publisher;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;

final readonly class MessengerEventPublisher implements EventPublisher
{
    public function __construct(
        private SenderInterface $transport,
    ) {
    }

    #[\Override]
    public function publish(IntegrationEvent $event): void
    {
        // TODO: add AmqpStamp($event->type) for routing key when symfony/amqp-messenger is installed
        $this->transport->send(new Envelope($event));
    }
}
