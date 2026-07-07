<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pipeline_job and pipeline_notification tables for background orchestration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE pipeline_job (
                id VARCHAR(36) NOT NULL,
                source_id VARCHAR(36) NOT NULL,
                video_id VARCHAR(36) DEFAULT NULL,
                audio_id VARCHAR(36) DEFAULT NULL,
                content_id VARCHAR(36) DEFAULT NULL,
                source_type VARCHAR(32) NOT NULL,
                stage VARCHAR(64) NOT NULL,
                status VARCHAR(64) NOT NULL,
                progress_percent INT NOT NULL DEFAULT 0,
                current_step VARCHAR(255) DEFAULT NULL,
                current_engine VARCHAR(128) DEFAULT NULL,
                provider VARCHAR(128) DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                estimated_duration_seconds INT DEFAULT NULL,
                estimated_remaining_seconds INT DEFAULT NULL,
                elapsed_seconds INT DEFAULT NULL,
                cancellation_reason TEXT DEFAULT NULL,
                failure_reason TEXT DEFAULT NULL,
                result_artifact_id VARCHAR(36) DEFAULT NULL,
                depends_on_stage VARCHAR(64) DEFAULT NULL,
                invalidates_stages JSON NOT NULL DEFAULT '[]',
                stale_artifact_ids JSON NOT NULL DEFAULT '[]',
                transcript_source VARCHAR(64) DEFAULT NULL,
                user_choice_required BOOLEAN NOT NULL DEFAULT FALSE,
                user_choice_options JSON NOT NULL DEFAULT '[]',
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_pipeline_job_source ON pipeline_job (source_id)');
        $this->addSql('CREATE INDEX idx_pipeline_job_source_stage ON pipeline_job (source_id, stage)');
        $this->addSql('CREATE INDEX idx_pipeline_job_status ON pipeline_job (status)');

        $this->addSql(<<<'SQL'
            CREATE TABLE pipeline_notification (
                id VARCHAR(36) NOT NULL,
                source_id VARCHAR(36) NOT NULL,
                stage VARCHAR(64) DEFAULT NULL,
                type VARCHAR(64) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                read_flag BOOLEAN NOT NULL DEFAULT FALSE,
                action_url VARCHAR(512) DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_pipeline_notification_source ON pipeline_notification (source_id)');

        $this->addSql('ALTER TABLE video_transcript ADD metadata JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pipeline_notification');
        $this->addSql('DROP TABLE pipeline_job');
        $this->addSql('ALTER TABLE video_transcript DROP metadata');
    }
}
