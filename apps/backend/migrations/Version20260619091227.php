<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619091227 extends AbstractMigration
{

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE paint_references (id UUID NOT NULL, handle TEXT NOT NULL, name VARCHAR(255) NOT NULL, brand_handle TEXT NOT NULL, range_handle TEXT NOT NULL, paint_type_handle TEXT NOT NULL, color_handle TEXT DEFAULT NULL, owned_by UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AE181643918020D9 ON paint_references (handle)');
        $this->addSql('CREATE TABLE paint_types (handle TEXT NOT NULL, name VARCHAR(255) NOT NULL, owned_by UUID NOT NULL, PRIMARY KEY (handle))');
        $this->addSql('CREATE TABLE paints (id UUID NOT NULL, paint_reference_id UUID NOT NULL, owned_by UUID NOT NULL, purchased_at DATE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE colors DROP CONSTRAINT colors_pkey');
        $this->addSql('ALTER TABLE colors DROP id');
        $this->addSql('ALTER TABLE colors RENAME COLUMN brand_handle TO handle');
        $this->addSql('ALTER TABLE colors ADD PRIMARY KEY (handle)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE paint_references');
        $this->addSql('DROP TABLE paint_types');
        $this->addSql('DROP TABLE paints');
        $this->addSql('ALTER TABLE colors DROP CONSTRAINT colors_pkey');
        $this->addSql('ALTER TABLE colors ADD id UUID NOT NULL');
        $this->addSql('ALTER TABLE colors RENAME COLUMN handle TO brand_handle');
        $this->addSql('ALTER TABLE colors ADD PRIMARY KEY (id)');
    }
}
