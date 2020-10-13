<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionCommunityForum5 extends AbstractMigration
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

        $this->addSql('ALTER TABLE Question ADD CONSTRAINT FK_4F812B189B6B5FBA FOREIGN KEY (account_id) REFERENCES AccountProfileContact (account_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Question ADD CONSTRAINT FK_4F812B1812469DE2 FOREIGN KEY (category_id) REFERENCES QuestionCategory (id)');
        $this->addSql('CREATE INDEX IDX_4F812B189B6B5FBA ON Question (account_id)');
        $this->addSql('ALTER TABLE Answer ADD CONSTRAINT FK_DD714F139B6B5FBA FOREIGN KEY (account_id) REFERENCES AccountProfileContact (account_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Answer ADD CONSTRAINT FK_DD714F131E27F6BF FOREIGN KEY (question_id) REFERENCES Question (id)');
        $this->addSql('CREATE INDEX IDX_DD714F139B6B5FBA ON Answer (account_id)');
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

        $this->addSql('ALTER TABLE Answer DROP FOREIGN KEY FK_DD714F139B6B5FBA');
        $this->addSql('ALTER TABLE Answer DROP FOREIGN KEY FK_DD714F131E27F6BF');
        $this->addSql('DROP INDEX IDX_DD714F139B6B5FBA ON Answer');
        $this->addSql('ALTER TABLE Question DROP FOREIGN KEY FK_4F812B189B6B5FBA');
        $this->addSql('ALTER TABLE Question DROP FOREIGN KEY FK_4F812B1812469DE2');
        $this->addSql('DROP INDEX IDX_4F812B189B6B5FBA ON Question');
    }
}
