<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contents table for Content aggregate';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE contents (
                id UUID NOT NULL,
                title VARCHAR(255) NOT NULL,
                source_type VARCHAR(32) NOT NULL,
                status VARCHAR(32) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contents');
    }
}
