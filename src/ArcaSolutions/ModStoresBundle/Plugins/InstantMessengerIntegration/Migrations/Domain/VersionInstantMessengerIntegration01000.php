<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Exception;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionInstantMessengerIntegration01000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws Exception
     */
    public function up(Schema $schema)
    {
        try {
            // this up() migration is auto-generated, please modify it to your needs
            $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
            if (!$schema->hasTable('Listing_InstantMessenger')) {
                $this->addSql('CREATE TABLE Listing_InstantMessenger (id INT AUTO_INCREMENT NOT NULL, listing_id INT DEFAULT NULL, import_id INT DEFAULT NULL, temp VARCHAR(255) NOT NULL, instant_messenger LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', UNIQUE INDEX UNIQ_908ED2F7D4619D1A (listing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
            }
            if (!$schema->hasTable('ListingLevel_InstantMessenger')) {
                $this->addSql('CREATE TABLE ListingLevel_InstantMessenger (id INT AUTO_INCREMENT NOT NULL, level INT DEFAULT NULL, instant_messenger VARCHAR(1) NOT NULL, UNIQUE INDEX UNIQ_7209631E9AEACC13 (level), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
            }
            if ($schema->hasTable('Listing_InstantMessenger')) {
                if (!$schema->getTable('Listing_InstantMessenger')->hasForeignKey('FK_908ED2F7D4619D1A')) {
                    $this->addSql('ALTER TABLE Listing_InstantMessenger ADD CONSTRAINT FK_908ED2F7D4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
                }
            }
            if ($schema->hasTable('ListingLevel_InstantMessenger')) {
                if (!$schema->getTable('ListingLevel_InstantMessenger')->hasForeignKey('FK_7209631E9AEACC13')) {
                    $this->addSql('ALTER TABLE ListingLevel_InstantMessenger ADD CONSTRAINT FK_7209631E9AEACC13 FOREIGN KEY (level) REFERENCES ListingLevel (value)');
                }
            }
        } catch (Exception $exc){
            throw $exc;
        }
    }
    
    /**
     * @param Schema $schema
     * @throws Exception
     */
    public function down(Schema $schema)
    {
        try {
            // this down() migration is auto-generated, please modify it to your needs
            $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    
            if ($schema->hasTable('Listing_InstantMessenger')) {
                $this->addSql('DROP TABLE Listing_InstantMessenger');
            }
            if ($schema->hasTable('ListingLevel_InstantMessenger')) {
                $this->addSql('DROP TABLE ListingLevel_InstantMessenger');
            }
        } catch (Exception $exc){
            throw $exc;
        }
    }
}
