<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260718120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stage_metadata JSON column to pipeline_job for user stage configuration.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pipeline_job ADD stage_metadata JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pipeline_job DROP stage_metadata');
    }
}
