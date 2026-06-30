<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create workspace project and batch job tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE workspace_projects (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, videos JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_workspace_projects_name ON workspace_projects (name)');
        $this->addSql('CREATE TABLE workspace_batch_jobs (id VARCHAR(36) NOT NULL, project_id VARCHAR(36) NOT NULL, video_ids JSON NOT NULL, target_languages JSON NOT NULL, status VARCHAR(32) NOT NULL, progress INT NOT NULL, outcomes JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_workspace_batch_jobs_project ON workspace_batch_jobs (project_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE workspace_batch_jobs');
        $this->addSql('DROP TABLE workspace_projects');
    }
}
