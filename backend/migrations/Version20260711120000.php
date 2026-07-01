<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pipeline telemetry records for Platform Sprint 49';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE pipeline_telemetry (
                id UUID NOT NULL,
                workspace_id UUID NOT NULL,
                payload JSON NOT NULL,
                recorded_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_pipeline_telemetry_workspace ON pipeline_telemetry (workspace_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pipeline_telemetry');
    }
}
