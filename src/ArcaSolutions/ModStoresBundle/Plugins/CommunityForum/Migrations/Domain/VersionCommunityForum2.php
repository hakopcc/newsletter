<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionCommunityForum2 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Question CHANGE user_id account_id INT(11) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE Answer CHANGE user_id account_id INT(11) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE QuestionUpvotes CHANGE user_id account_id INT(11) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE AnswerUpvotes CHANGE user_id account_id INT(11) NULL DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Question CHANGE account_id user_id INT(11) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE Answer CHANGE account_id user_id INT(11) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE QuestionUpvotes CHANGE account_id user_id INT(11) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE AnswerUpvotes CHANGE account_id user_id INT(11) NULL DEFAULT NULL');
    }
}
