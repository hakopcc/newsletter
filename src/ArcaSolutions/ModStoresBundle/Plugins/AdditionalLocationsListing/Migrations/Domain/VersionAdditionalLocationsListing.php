<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalLocationsListing extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS Listing_ExtraLocation (id INT AUTO_INCREMENT NOT NULL, listing_id INT NOT NULL, loc_location_1 INT DEFAULT 0 NOT NULL, loc_location_2 INT DEFAULT 0 NOT NULL, loc_location_3 INT DEFAULT 0 NOT NULL, loc_location_4 INT DEFAULT 0 NOT NULL, loc_location_5 INT DEFAULT 0 NOT NULL, loc_address VARCHAR(50) NOT NULL, loc_address2 VARCHAR(50) NOT NULL, loc_zip_code VARCHAR(10) NOT NULL, loc_zip5 VARCHAR(10) DEFAULT NULL, loc_latitude VARCHAR(50) DEFAULT NULL, loc_longitude VARCHAR(50) DEFAULT NULL, loc_map_tuning VARCHAR(255) DEFAULT NULL, loc_map_zoom INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS ListingLevel_Locations (id INT AUTO_INCREMENT NOT NULL, listing_level INT DEFAULT NULL, extralocations INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ExtraLocation');
        $this->addSql('DROP TABLE ListingLevel_Locations');
    }
}
