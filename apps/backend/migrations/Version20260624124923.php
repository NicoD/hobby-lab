<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260624124923 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'ADR-008: create catalog_* and stash_* tables, drop old flat ColorLab tables';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE catalog_brands (ranges JSON NOT NULL, handle TEXT NOT NULL, name VARCHAR(255) NOT NULL, owned_by UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (handle))');
        $this->addSql('CREATE TABLE catalog_colors (handle TEXT NOT NULL, name VARCHAR(255) NOT NULL, owned_by UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (handle))');
        $this->addSql('CREATE TABLE catalog_paint_types (handle TEXT NOT NULL, name VARCHAR(255) NOT NULL, owned_by UUID NOT NULL, PRIMARY KEY (handle))');
        $this->addSql('CREATE TABLE catalog_paints (handle TEXT NOT NULL, name VARCHAR(255) NOT NULL, brand_handle TEXT DEFAULT NULL, range_handle TEXT DEFAULT NULL, paint_type_handle TEXT DEFAULT NULL, color_handle TEXT DEFAULT NULL, user_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (handle))');
        $this->addSql('CREATE TABLE stash_paints (id UUID NOT NULL, paint_handle TEXT NOT NULL, owned_by UUID NOT NULL, purchased_at DATE DEFAULT NULL, PRIMARY KEY (id))');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE catalog_brands');
        $this->addSql('DROP TABLE catalog_colors');
        $this->addSql('DROP TABLE catalog_paint_types');
        $this->addSql('DROP TABLE catalog_paints');
        $this->addSql('DROP TABLE stash_paints');
    }
}
