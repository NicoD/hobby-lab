<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260623000000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE paint_references DROP CONSTRAINT paint_references_pkey');
        $this->addSql('ALTER TABLE paint_references DROP COLUMN id');
        $this->addSql('ALTER TABLE paint_references ADD PRIMARY KEY (handle)');
        $this->addSql('ALTER TABLE paints DROP COLUMN paint_reference_id');
        $this->addSql('ALTER TABLE paints ADD COLUMN paint_reference_handle VARCHAR(255) NOT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE paints DROP COLUMN paint_reference_handle');
        $this->addSql('ALTER TABLE paints ADD COLUMN paint_reference_id UUID NOT NULL');
        $this->addSql('ALTER TABLE paint_references DROP CONSTRAINT paint_references_pkey');
        $this->addSql('ALTER TABLE paint_references ADD COLUMN id UUID NOT NULL');
        $this->addSql('ALTER TABLE paint_references ADD PRIMARY KEY (id)');
    }
}
