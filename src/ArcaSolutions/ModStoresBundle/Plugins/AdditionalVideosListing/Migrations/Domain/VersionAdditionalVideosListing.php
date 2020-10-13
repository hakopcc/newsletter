<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalVideosListing extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS ListingLevel_Videos (level_id INT(1) UNSIGNED NULL DEFAULT NULL, module VARCHAR(50) NULL DEFAULT NULL, videos TINYINT(3) UNSIGNED NOT NULL DEFAULT 0, INDEX level_id_module (level_id, module) ) COLLATE=utf8_general_ci ENGINE=InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS Listing_Videos (id INT(1) UNSIGNED NOT NULL AUTO_INCREMENT, listing_id INT(11) NOT NULL, video_snippet TEXT NULL, video_url TEXT NULL, video_description TEXT NULL, PRIMARY KEY (id), INDEX listing_id (listing_id) ) COLLATE=utf8_general_ci ENGINE=InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ListingLevel_Videos');
        $this->addSql('DROP TABLE Listing_Videos');
    }
}
