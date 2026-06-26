<?php

declare(strict_types=1);

namespace Outbox;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

final readonly class PostgresConnection
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        if (!$connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            throw new \RuntimeException('OutboxBundle requires PostgreSQL — LISTEN/NOTIFY and SKIP LOCKED are not portable.');
        }

        $this->connection = $connection;
    }

    /** @param array<string, mixed> $data */
    public function insert(string $table, array $data): void
    {
        $this->connection->insert($table, $data);
    }

    /** @param array<int<0, max>|string, mixed> $params */
    public function executeStatement(string $sql, array $params = []): void
    {
        $this->connection->executeStatement($sql, $params);
    }

    /**
     * @param array<int<0, max>|string, mixed> $params
     *
     * @return array<string, mixed>|false
     */
    public function fetchAssociative(string $sql, array $params = []): array|false
    {
        return $this->connection->fetchAssociative($sql, $params);
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function isTransactionActive(): bool
    {
        return $this->connection->isTransactionActive();
    }

    public function getNativeConnection(): \Pdo\Pgsql
    {
        $pdo = $this->connection->getNativeConnection();
        \assert($pdo instanceof \Pdo\Pgsql);

        return $pdo;
    }
}
