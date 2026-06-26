<?php

declare(strict_types=1);

namespace Outbox\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626000000_Outbox extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE outbox_events (
                id                  UUID        PRIMARY KEY,
                domain_type         VARCHAR     NOT NULL,
                domain_payload      JSONB       NOT NULL,
                integration_payload JSONB,
                occurred_at         TIMESTAMPTZ NOT NULL,
                mapped_at           TIMESTAMPTZ,
                status              VARCHAR     NOT NULL DEFAULT 'pending'
                                        CHECK (status IN ('pending', 'mapped', 'sent', 'failed')),
                attempt             SMALLINT    NOT NULL DEFAULT 0,
                next_retry_at       TIMESTAMPTZ,
                last_error          TEXT,
                sent_at             TIMESTAMPTZ,
                created_at          TIMESTAMPTZ NOT NULL DEFAULT now()
            )
            SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX outbox_events_processable_idx ON outbox_events (status, next_retry_at)
                WHERE status IN ('pending', 'mapped')
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE outbox_events');
    }
}
