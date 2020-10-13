<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionCommunityForum6 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws DBALException
     * @throws AbortMigrationException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE QuestionCategory ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE QuestionCategory ADD CONSTRAINT FK_9291348A54B9D732 FOREIGN KEY (icon_id) REFERENCES Image (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9291348A54B9D732 ON QuestionCategory (icon_id)');
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     * @throws AbortMigrationException
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE QuestionCategory DROP FOREIGN KEY FK_9291348A54B9D732');
        $this->addSql('DROP INDEX UNIQ_9291348A54B9D732 ON QuestionCategory');
        $this->addSql('ALTER TABLE QuestionCategory DROP icon_id');
    }
}
