<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create video_final_render table for final rendered videos';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE video_final_render (video_id VARCHAR(36) NOT NULL, target_language VARCHAR(32) NOT NULL, payload TEXT NOT NULL, PRIMARY KEY(video_id, target_language))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE video_final_render');
    }
}
