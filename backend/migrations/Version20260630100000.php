<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add documents JSON column to conversation for multi-document selection';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE conversation ADD documents JSON NOT NULL DEFAULT '[]'");
        $this->addSql(<<<'SQL'
            UPDATE conversation
            SET documents = json_build_array(json_build_object('contentId', content_id::text))
            SQL);
        $this->addSql('ALTER TABLE conversation ALTER COLUMN documents DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation DROP COLUMN documents');
    }
}
