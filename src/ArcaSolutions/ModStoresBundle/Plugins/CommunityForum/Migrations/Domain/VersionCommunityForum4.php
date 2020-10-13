<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionCommunityForum4 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE QuestionCategory DROP root_id');
        $this->addSql('ALTER TABLE QuestionCategory DROP `left`');
        $this->addSql('ALTER TABLE QuestionCategory DROP `right`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE QuestionCategory ADD root_id INT NULL AFTER id');
        $this->addSql('ALTER TABLE QuestionCategory ADD `left` INT NULL AFTER root_id');
        $this->addSql('ALTER TABLE QuestionCategory ADD `right` INT NULL AFTER `left`');
    }
}
