<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionArticleAssociationListing3 extends AbstractMigration
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

        $this->addSql('DROP INDEX uniq_df44f2b871f7e87b ON ArticleAssociated');
        $this->addSql('ALTER TABLE ArticleAssociated ADD CONSTRAINT FK_258290C57294869C FOREIGN KEY (article_id) REFERENCES Article (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_258290C57294869C ON ArticleAssociated (article_id)');
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

        $this->addSql('DROP INDEX uniq_258290c57294869c ON ArticleAssociated');
        $this->addSql('ALTER TABLE ArticleAssociated DROP FOREIGN KEY FK_258290C57294869C');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DF44F2B871F7E87B ON ArticleAssociated (article_id)');
        $this->addSql('ALTER TABLE ArticleAssociated ADD CONSTRAINT FK_258290C57294869C FOREIGN KEY (article_id) REFERENCES Article (id)');
    }
}
