<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create artifacts table for Artifact aggregate';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE artifacts (
                id UUID NOT NULL,
                content_id UUID NOT NULL,
                processing_job_id UUID NOT NULL,
                type VARCHAR(32) NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE INDEX idx_artifacts_content_id ON artifacts (content_id)');
        $this->addSql('CREATE INDEX idx_artifacts_processing_job_id ON artifacts (processing_job_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE artifacts');
    }
}
