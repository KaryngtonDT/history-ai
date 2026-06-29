<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add video_job table for uploaded video processing jobs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE video_job (
                id CHAR(36) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                language VARCHAR(32) NOT NULL,
                status VARCHAR(32) NOT NULL,
                storage_path VARCHAR(512) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE video_job');
    }
}
