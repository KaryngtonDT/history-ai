<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create library_items table for LibraryItem aggregate';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE library_items (
                id UUID NOT NULL,
                content_id UUID NOT NULL,
                artifact_id UUID NOT NULL,
                type VARCHAR(32) NOT NULL,
                title VARCHAR(255) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE INDEX idx_library_items_content_id ON library_items (content_id)');
        $this->addSql('CREATE INDEX idx_library_items_artifact_id ON library_items (artifact_id)');
        $this->addSql('CREATE INDEX idx_library_items_type ON library_items (type)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE library_items');
    }
}
