<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionArticleAssociationListing2 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE ListingLevel_Articles TO ListingLevel_FieldArticles');
        $this->addSql('ALTER TABLE ListingLevel_FieldArticles CHANGE listing_level `level` INT(11) NOT NULL, CHANGE articles field INT(11) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE ListingLevel_FieldArticles TO ListingLevel_Articles');
        $this->addSql('ALTER TABLE ListingLevel_Articles CHANGE `level` listing_level INT(11) DEFAULT NULL, CHANGE field articles INT(11) DEFAULT NULL');
    }
}
