<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260713120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add youtube_import table for Platform Sprint 52';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE youtube_import (
                id UUID NOT NULL,
                video_id UUID NOT NULL,
                youtube_url VARCHAR(512) NOT NULL,
                title VARCHAR(255) NOT NULL,
                duration_seconds INT DEFAULT NULL,
                thumbnail_url VARCHAR(512) DEFAULT NULL,
                language VARCHAR(16) DEFAULT NULL,
                channel_name VARCHAR(255) DEFAULT NULL,
                imported_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_youtube_import_video ON youtube_import (video_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE youtube_import');
    }
}
