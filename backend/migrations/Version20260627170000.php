<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260627170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create collections table for Collection aggregate';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE collections (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE INDEX idx_collections_name ON collections (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE collections');
    }
}
