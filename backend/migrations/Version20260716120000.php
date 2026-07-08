<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add engine_execution_history for adaptive duration learning';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE engine_execution_history (
                id UUID NOT NULL,
                pipeline_job_id UUID NOT NULL,
                source_id UUID NOT NULL,
                stage VARCHAR(64) NOT NULL,
                engine_id VARCHAR(128) NOT NULL,
                engine_version VARCHAR(64) DEFAULT NULL,
                provider VARCHAR(128) DEFAULT NULL,
                hardware_profile VARCHAR(64) NOT NULL,
                model VARCHAR(128) DEFAULT NULL,
                language VARCHAR(32) DEFAULT NULL,
                media_duration_seconds INT DEFAULT NULL,
                input_size_bytes INT DEFAULT NULL,
                output_size_bytes INT DEFAULT NULL,
                estimated_duration_seconds INT NOT NULL,
                actual_duration_seconds INT NOT NULL,
                estimation_error_seconds INT NOT NULL,
                estimation_accuracy_percent DOUBLE PRECISION NOT NULL,
                started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                completed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                status VARCHAR(32) NOT NULL,
                benchmark_score DOUBLE PRECISION DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_engine_execution_stage ON engine_execution_history (stage)');
        $this->addSql('CREATE INDEX idx_engine_execution_engine ON engine_execution_history (engine_id)');
        $this->addSql('CREATE INDEX idx_engine_execution_hardware ON engine_execution_history (hardware_profile)');
        $this->addSql('CREATE INDEX idx_engine_execution_job ON engine_execution_history (pipeline_job_id)');
        $this->addSql('CREATE INDEX idx_engine_execution_completed ON engine_execution_history (completed_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE engine_execution_history');
    }
}
