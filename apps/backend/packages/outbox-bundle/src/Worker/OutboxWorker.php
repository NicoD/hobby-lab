<?php

declare(strict_types=1);

namespace Outbox\Worker;

use Outbox\PostgresConnection;
use Outbox\Publisher\EventPublisher;
use Outbox\Publisher\IntegrationEvent;
use Outbox\Publisher\IntegrationEventResolverInterface;

final readonly class OutboxWorker
{
    private const int MAX_ATTEMPTS = 5;

    public function __construct(
        private PostgresConnection $connection,
        private IntegrationEventResolverInterface $resolver,
        private EventPublisher $publisher,
    ) {
    }

    public function run(): void
    {
        $pdo = $this->connection->getNativeConnection();
        $pdo->exec('LISTEN outbox_new_event');

        /* @phpstan-ignore while.alwaysTrue */
        while (true) {
            $pdo->getNotify(\PDO::FETCH_ASSOC, 300_000);
            $this->process();
        }
    }

    private function process(): void
    {
        do {
            $processed = $this->resolvePending();
        } while ($processed);

        do {
            $processed = $this->publishMapped();
        } while ($processed);
    }

    private function resolvePending(): bool
    {
        $this->connection->beginTransaction();
        $rowId = null;

        try {
            $row = $this->connection->fetchAssociative(
                "SELECT id, domain_type, domain_payload, correlation_id FROM outbox_events
                 WHERE status = 'pending' AND (next_retry_at IS NULL OR next_retry_at <= now())
                 ORDER BY created_at
                 FOR UPDATE SKIP LOCKED
                 LIMIT 1",
            );

            if (false === $row) {
                $this->connection->commit();

                return false;
            }

            /** @var array{id: string, domain_type: string, domain_payload: string, correlation_id: string} $row */
            $rowId = $row['id'];

            /** @var array<string, mixed> $domainPayload */
            $domainPayload = json_decode($row['domain_payload'], true, 512, \JSON_THROW_ON_ERROR);
            $integrationPayload = $this->resolver->resolve($row['domain_type'], $domainPayload);

            if (null === $integrationPayload) {
                $this->connection->executeStatement(
                    "UPDATE outbox_events SET status = 'sent', sent_at = now() WHERE id = :id",
                    ['id' => $rowId],
                );
                $this->connection->commit();

                return true;
            }

            $this->connection->executeStatement(
                "UPDATE outbox_events
                 SET status = 'mapped',
                     integration_payload = :payload,
                     mapped_at = now()
                 WHERE id = :id",
                [
                    'payload' => json_encode($integrationPayload, \JSON_THROW_ON_ERROR),
                    'id' => $rowId,
                ],
            );

            $this->connection->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            if (null !== $rowId) {
                $this->scheduleRetry($rowId, $e->getMessage());
            }

            return false;
        }
    }

    private function publishMapped(): bool
    {
        $this->connection->beginTransaction();
        $rowId = null;

        try {
            $row = $this->connection->fetchAssociative(
                "SELECT id, domain_type, integration_payload, correlation_id FROM outbox_events
                 WHERE status = 'mapped' AND (next_retry_at IS NULL OR next_retry_at <= now())
                 ORDER BY mapped_at
                 FOR UPDATE SKIP LOCKED
                 LIMIT 1",
            );

            if (false === $row) {
                $this->connection->commit();

                return false;
            }

            /** @var array{id: string, domain_type: string, integration_payload: string, correlation_id: string} $row */
            $rowId = $row['id'];

            /** @var array<string, mixed> $integrationPayload */
            $integrationPayload = json_decode($row['integration_payload'], true, 512, \JSON_THROW_ON_ERROR);

            $this->publisher->publish(new IntegrationEvent(
                $rowId,
                $row['domain_type'],
                $integrationPayload,
                $row['correlation_id'],
            ));

            $this->connection->executeStatement(
                "UPDATE outbox_events SET status = 'sent', sent_at = now() WHERE id = :id",
                ['id' => $rowId],
            );

            $this->connection->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            if (null !== $rowId) {
                $this->scheduleRetry($rowId, $e->getMessage());
            }

            return false;
        }
    }

    private function scheduleRetry(string $id, string $error): void
    {
        $row = $this->connection->fetchAssociative(
            'SELECT attempt FROM outbox_events WHERE id = :id',
            ['id' => $id],
        );

        if (false === $row) {
            return;
        }

        /** @var array{attempt: string} $row */
        $nextAttempt = (int) $row['attempt'] + 1;

        if ($nextAttempt >= self::MAX_ATTEMPTS) {
            $this->connection->executeStatement(
                "UPDATE outbox_events
                 SET attempt = :attempt, last_error = :error, status = 'failed', next_retry_at = NULL
                 WHERE id = :id",
                ['attempt' => $nextAttempt, 'error' => $error, 'id' => $id],
            );

            return;
        }

        $backoffSeconds = 30 * (2 ** $nextAttempt);

        $this->connection->executeStatement(
            'UPDATE outbox_events SET attempt = :attempt, last_error = :error, next_retry_at = :next_retry_at WHERE id = :id',
            [
                'attempt' => $nextAttempt,
                'error' => $error,
                'next_retry_at' => new \DateTimeImmutable()->modify("+{$backoffSeconds} seconds")->format('Y-m-d H:i:s.u P'),
                'id' => $id,
            ],
        );
    }
}
