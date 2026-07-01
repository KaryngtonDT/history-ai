<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260712120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add source table for Platform Sprint 51 audio ingestion';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE source (
                id UUID NOT NULL,
                type VARCHAR(32) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                title VARCHAR(255) DEFAULT NULL,
                language VARCHAR(16) DEFAULT NULL,
                status VARCHAR(32) NOT NULL,
                storage_path VARCHAR(512) NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_source_type_status ON source (type, status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE source');
    }
}
