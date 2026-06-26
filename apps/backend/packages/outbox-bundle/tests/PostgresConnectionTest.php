<?php

declare(strict_types=1);

namespace OutboxTest;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Outbox\PostgresConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PostgresConnectionTest extends TestCase
{
    #[Test]
    public function it_rejects_non_postgresql_connections(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new MySQLPlatform());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/PostgreSQL/');

        new PostgresConnection($connection);
    }
}
