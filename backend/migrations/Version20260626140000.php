<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create processing_jobs table for ProcessingJob aggregate';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE processing_jobs (
                id UUID NOT NULL,
                content_id UUID NOT NULL,
                type VARCHAR(32) NOT NULL,
                status VARCHAR(32) NOT NULL,
                progress SMALLINT NOT NULL,
                started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                failed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE INDEX idx_processing_jobs_content_id ON processing_jobs (content_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE processing_jobs');
    }
}
