<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create video execution history table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE video_execution_histories (id VARCHAR(36) NOT NULL, video_id VARCHAR(36) NOT NULL, versions JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_video_execution_histories_video ON video_execution_histories (video_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE video_execution_histories');
    }
}
