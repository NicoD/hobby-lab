<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Messenger;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class MessengerBus implements CommandBus, QueryBus
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[\Override]
    public function handle(object $message): mixed
    {
        try {
            $envelope = $this->bus->dispatch($message);
        } catch (HandlerFailedException $e) {
            foreach ($e->getWrappedExceptions() as $nested) {
                throw $nested;
            }

            throw $e;
        }

        $stamps = $envelope->all(HandledStamp::class);

        if ([] === $stamps) {
            throw new LogicException(\sprintf('Message "%s" was handled zero times. Exactly one handler is expected.', get_debug_type($message)));
        }

        if (\count($stamps) > 1) {
            $handlers = implode(', ', array_map(
                static fn (HandledStamp $s): string => \sprintf('"%s"', $s->getHandlerName()),
                $stamps,
            ));

            throw new LogicException(\sprintf('Message "%s" was handled %d times. Exactly one handler is expected, got: %s.', get_debug_type($message), \count($stamps), $handlers));
        }

        return $stamps[0]->getResult();
    }
}
