<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260624000002 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE paint_references ADD COLUMN created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW()");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE paint_references DROP COLUMN created_at');
    }
}
