<?php

declare(strict_types=1);

namespace OutboxTest\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Outbox\Adapter\DoctrineOutboxAdapter;
use Outbox\OutboxMessage;
use Outbox\PostgresConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DoctrineOutboxAdapterTest extends TestCase
{
    #[Test]
    public function it_inserts_one_row_per_message(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());
        $connection->expects($this->exactly(2))->method('insert');

        $adapter = new DoctrineOutboxAdapter(new PostgresConnection($connection));
        $adapter->record($this->aMessage(), $this->aMessage());
    }

    #[Test]
    public function it_maps_message_fields_to_outbox_columns(): void
    {
        $id = Uuid::v7();
        $occurredAt = new \DateTimeImmutable('2026-06-26 12:00:00.000000 +00:00');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());
        $connection->expects($this->once())
            ->method('insert')
            ->with('outbox_events', $this->callback(function (array $row) use ($id, $occurredAt): bool {
                return $row['id'] === $id->toRfc4122()
                    && $row['domain_type'] === 'colorlab.brand.created'
                    && $row['domain_payload'] === '{"brand_handle":"test-brand","name":"Vallejo"}'
                    && $row['occurred_at'] === $occurredAt->format('Y-m-d H:i:s.u P');
            }));

        $adapter = new DoctrineOutboxAdapter(new PostgresConnection($connection));
        $adapter->record(new OutboxMessage(
            $id->toRfc4122(),
            'colorlab.brand.created',
            ['brand_handle' => 'test-brand', 'name' => 'Vallejo'],
            $occurredAt,
        ));
    }

    #[Test]
    public function it_does_nothing_when_called_with_no_messages(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());
        $connection->expects($this->never())->method('insert');

        $adapter = new DoctrineOutboxAdapter(new PostgresConnection($connection));
        $adapter->record();
    }

    private function aMessage(): OutboxMessage
    {
        return new OutboxMessage(
            Uuid::v7()->toRfc4122(),
            'some.domain.event',
            ['key' => 'value'],
            new \DateTimeImmutable(),
        );
    }
}
