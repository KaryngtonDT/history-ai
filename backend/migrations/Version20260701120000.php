<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create video_translation table for multilingual translation persistence';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE video_translation (
                video_id VARCHAR(36) NOT NULL,
                target_language VARCHAR(32) NOT NULL,
                payload TEXT NOT NULL,
                PRIMARY KEY (video_id, target_language)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE video_translation');
    }
}
