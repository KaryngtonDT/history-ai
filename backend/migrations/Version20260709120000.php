<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add video reviews and user preference profiles for Platform Sprint 47';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE video_reviews (
                id UUID NOT NULL,
                video_id UUID NOT NULL,
                execution_version_number INT NOT NULL,
                scores JSON NOT NULL,
                comment TEXT NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_video_reviews_video_id ON video_reviews (video_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE user_preference_profiles (
                id VARCHAR(32) NOT NULL,
                translation_style VARCHAR(32) NOT NULL,
                voice_stability VARCHAR(32) NOT NULL,
                rendering_preset VARCHAR(32) NOT NULL,
                lip_sync_strength VARCHAR(32) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE video_reviews');
        $this->addSql('DROP TABLE user_preference_profiles');
    }
}
