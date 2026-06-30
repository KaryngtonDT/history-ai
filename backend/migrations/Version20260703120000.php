<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create video_voice_clone table for voice clone artifacts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE video_voice_clone (video_id VARCHAR(36) NOT NULL, target_language VARCHAR(32) NOT NULL, payload TEXT NOT NULL, PRIMARY KEY(video_id, target_language))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE video_voice_clone');
    }
}
