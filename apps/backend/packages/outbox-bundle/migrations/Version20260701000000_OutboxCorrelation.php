<?php

declare(strict_types=1);

namespace Outbox\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701000000_OutboxCorrelation extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE outbox_events ADD COLUMN correlation_id UUID NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE outbox_events DROP COLUMN correlation_id');
    }
}
