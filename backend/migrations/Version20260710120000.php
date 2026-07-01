<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add workspace members and invitations for Platform Sprint 48';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE workspace_members (
                id UUID NOT NULL,
                workspace_id UUID NOT NULL,
                user_id VARCHAR(255) NOT NULL,
                display_name VARCHAR(255) NOT NULL,
                role VARCHAR(32) NOT NULL,
                joined_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_workspace_member_user ON workspace_members (workspace_id, user_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE workspace_invitations (
                id UUID NOT NULL,
                workspace_id UUID NOT NULL,
                email VARCHAR(255) NOT NULL,
                role VARCHAR(32) NOT NULL,
                token VARCHAR(128) NOT NULL,
                status VARCHAR(32) NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_workspace_invitation_token ON workspace_invitations (token)');
        $this->addSql('CREATE INDEX idx_workspace_invitations_workspace ON workspace_invitations (workspace_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE workspace_invitations');
        $this->addSql('DROP TABLE workspace_members');
    }
}
