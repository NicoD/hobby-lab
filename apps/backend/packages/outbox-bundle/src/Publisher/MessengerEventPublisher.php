<?php

declare(strict_types=1);

namespace Outbox\Publisher;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
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
        $this->transport->send((new Envelope($event))->with(new AmqpStamp($event->type)));
    }
}
