<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdvancedSocialMediaListing2 extends AbstractMigration
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

        //Clean nonexistent relationship to add Foreign Key.
        $this->addSql('DELETE FROM Listing_SocialMedia WHERE listing_id NOT IN (SELECT id FROM Listing)');

        $this->addSql('ALTER TABLE Listing_SocialMedia ADD CONSTRAINT FK_4EDF0DE6D4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
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

        $this->addSql('ALTER TABLE Listing_SocialMedia DROP FOREIGN KEY FK_4EDF0DE6D4619D1A');
    }
}
