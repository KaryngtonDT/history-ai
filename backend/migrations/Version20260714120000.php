<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260714120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pipeline failure diagnostics columns to video_job';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video_job ADD failure_message TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE video_job ADD failed_stage VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE video_job ADD last_processing_duration_seconds DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video_job DROP failure_message');
        $this->addSql('ALTER TABLE video_job DROP failed_stage');
        $this->addSql('ALTER TABLE video_job DROP last_processing_duration_seconds');
    }
}
