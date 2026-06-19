<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Model\AggregateRoot;
use Psr\Log\LoggerInterface;

final readonly class TransactionManager
{
    public function __construct(
        private TransactionBoundary $transaction,
        private DomainEventDispatcher $domainEventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Closure(): (AggregateRoot|iterable<AggregateRoot>) $fn
     */
    public function execute(\Closure $fn): void
    {
        \assert(false === $this->transaction->isTransactionActive());

        $this->transaction->begin();

        try {
            $result = $fn();
            $aggregates = $result instanceof AggregateRoot
                ? [$result]
                : array_values(iterator_to_array($result));

            $events = $this->collectEvents($aggregates);

            // TODO: Outbox — write integration events in this transaction, before commit.
            // $this->outbox->record(...$this->translator->translate($events));

            $this->transaction->commit();
        } catch (\Throwable $e) {
            $this->logger->error('Transaction failed in {class}: {message}', [
                'class' => self::class,
                'message' => $e->getMessage(),
            ]);

            $this->transaction->rollback();

            throw $e;
        }

        $this->domainEventDispatcher->dispatch(...$events);
    }

    /**
     * @param list<AggregateRoot> $aggregates
     *
     * @return list<DomainEvent>
     */
    private function collectEvents(array $aggregates): array
    {
        return array_merge(
            ...array_map(
                static fn (AggregateRoot $a): array => array_values(iterator_to_array($a->pullDomainEvents())),
                $aggregates,
            ),
        );
    }
}
