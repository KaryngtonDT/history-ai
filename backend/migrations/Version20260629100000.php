<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create conversation table for persistent chat history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE conversation (
                id UUID NOT NULL,
                content_id UUID NOT NULL,
                messages JSON NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE INDEX idx_conversation_content_id ON conversation (content_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE conversation');
    }
}
