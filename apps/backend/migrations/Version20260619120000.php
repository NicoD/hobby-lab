<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260619120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE paint_references ALTER COLUMN brand_handle DROP NOT NULL');
        $this->addSql('ALTER TABLE paint_references ALTER COLUMN range_handle DROP NOT NULL');
        $this->addSql('ALTER TABLE paint_references ALTER COLUMN paint_type_handle DROP NOT NULL');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE paint_references ALTER COLUMN brand_handle SET NOT NULL');
        $this->addSql('ALTER TABLE paint_references ALTER COLUMN range_handle SET NOT NULL');
        $this->addSql('ALTER TABLE paint_references ALTER COLUMN paint_type_handle SET NOT NULL');
    }
}
