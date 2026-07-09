<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pipeline_job.progress_detail JSON for live progress metadata';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pipeline_job ADD progress_detail JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pipeline_job DROP progress_detail');
    }
}
