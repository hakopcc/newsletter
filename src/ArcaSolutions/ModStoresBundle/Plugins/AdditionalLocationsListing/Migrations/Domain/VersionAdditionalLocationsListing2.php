<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalLocationsListing2 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE ListingLevel_Locations TO ListingLevel_FieldLocations');
        $this->addSql('ALTER TABLE ListingLevel_FieldLocations CHANGE listing_level `level` INT(11) NOT NULL, CHANGE extralocations field INT(11) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE ListingLevel_FieldLocations TO ListingLevel_Locations');
        $this->addSql('ALTER TABLE ListingLevel_Locations CHANGE `level` listing_level INT(11) NULL DEFAULT NULL, CHANGE field extralocations INT(11) NULL DEFAULT NULL');
    }
}
