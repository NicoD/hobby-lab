<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Testing;

use App\Shared\Application\Service\DomainEventDispatcher;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Event\SymfonyDomainEventDispatcher;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Autoconfigure(public: true)]
#[AsDecorator(decorates: SymfonyDomainEventDispatcher::class)]
final class CollectingEventDispatcher implements DomainEventDispatcher
{
    /** @var list<DomainEvent> */
    private array $collected = [];

    public function __construct(private readonly SymfonyDomainEventDispatcher $inner)
    {
    }

    #[\Override]
    public function dispatch(DomainEvent ...$events): void
    {
        $this->collected = array_merge($this->collected, array_values($events));
        $this->inner->dispatch(...$events);
    }

    /**
     * @template T of DomainEvent
     *
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    public function ofType(string $class): array
    {
        /* @var list<T> */
        return array_values(array_filter($this->collected, static fn (DomainEvent $e): bool => $e instanceof $class));
    }

    public function reset(): void
    {
        $this->collected = [];
    }
}
