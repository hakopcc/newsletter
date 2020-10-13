<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalLocationsListing3 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws DBALException
     * @throws AbortMigrationException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing_ExtraLocation CHANGE loc_address loc_address VARCHAR(50) DEFAULT NULL, CHANGE loc_address2 loc_address2 VARCHAR(50) DEFAULT NULL, CHANGE loc_zip_code loc_zip_code VARCHAR(10) DEFAULT NULL, CHANGE loc_zip5 loc_zip5 VARCHAR(10) DEFAULT NULL, CHANGE loc_latitude loc_latitude VARCHAR(50) DEFAULT NULL, CHANGE loc_longitude loc_longitude VARCHAR(50) DEFAULT NULL, CHANGE loc_map_tuning loc_map_tuning VARCHAR(255) DEFAULT NULL, CHANGE loc_map_zoom loc_map_zoom INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     * @throws AbortMigrationException
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing_ExtraLocation CHANGE loc_address loc_address VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE loc_address2 loc_address2 VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE loc_zip_code loc_zip_code VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE loc_zip5 loc_zip5 VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE loc_latitude loc_latitude VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE loc_longitude loc_longitude VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE loc_map_tuning loc_map_tuning VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE loc_map_zoom loc_map_zoom INT NOT NULL');
    }
}
