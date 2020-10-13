<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalVideosArticles5 extends AbstractMigration
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
        if ($schema->hasTable('Article_Videos')) {
            if ($schema->getTable('Article_Videos')->hasIndex('article_id')) {
                $this->addSql('DROP INDEX article_id ON Article_Videos');
            }
        }
        $this->addSql('ALTER TABLE Article_Videos ADD CONSTRAINT FK_89A58E5C7294869C FOREIGN KEY (article_id) REFERENCES Article (id)');
        $this->addSql('CREATE INDEX IDX_89A58E5C7294869C ON Article_Videos (article_id)');
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

        $this->addSql('ALTER TABLE Article_Videos DROP FOREIGN KEY FK_89A58E5C7294869C');
        $this->addSql('DROP INDEX idx_89a58e5c7294869c ON Article_Videos');
        $this->addSql('CREATE INDEX article_id ON Article_Videos (article_id)');
    }
}
