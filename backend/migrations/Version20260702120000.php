<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260702120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create video_audio table for text-to-speech audio persistence';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE video_audio (
                video_id VARCHAR(36) NOT NULL,
                target_language VARCHAR(32) NOT NULL,
                payload TEXT NOT NULL,
                PRIMARY KEY (video_id, target_language)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE video_audio');
    }
}
