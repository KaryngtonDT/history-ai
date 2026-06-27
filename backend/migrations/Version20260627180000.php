<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260627180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create collection_items table for CollectionItem association aggregate';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE collection_items (
                id UUID NOT NULL,
                collection_id UUID NOT NULL,
                library_item_id UUID NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_collection_items_collection_library_item ON collection_items (collection_id, library_item_id)');
        $this->addSql('CREATE INDEX idx_collection_items_collection_id ON collection_items (collection_id)');
        $this->addSql('CREATE INDEX idx_collection_items_library_item_id ON collection_items (library_item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE collection_items');
    }
}
