<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Correlation;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

final readonly class CorrelationRequestListener implements EventSubscriberInterface
{
    public function __construct(private CorrelationContext $correlationContext)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100],
            KernelEvents::RESPONSE => 'onResponse',
            KernelEvents::TERMINATE => 'onTerminate',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $raw = $event->getRequest()->headers->get('X-Correlation-Id');
        $correlationId = (null !== $raw && Uuid::isValid($raw)) ? $raw : Uuid::v4()->toRfc4122();
        $event->getRequest()->headers->set('X-Correlation-Id', $correlationId);
        $this->correlationContext->set($correlationId);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $correlationId = $event->getRequest()->headers->get('X-Correlation-Id');
        if (null !== $correlationId) {
            $event->getResponse()->headers->set('X-Correlation-Id', $correlationId);
        }
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $this->correlationContext->clear();
    }
}
