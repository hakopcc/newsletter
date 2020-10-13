<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionEnhancedLead3 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE ListingLevel_EnhancedLead TO ListingLevel_FieldLeads');
        $this->addSql('ALTER TABLE ListingLevel_FieldLeads CHANGE listing_level `level` INT(11) NOT NULL, CHANGE leads field INT(11) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE ListingLevel_FieldLeads TO ListingLevel_EnhancedLead');
        $this->addSql('ALTER TABLE ListingLevel_EnhancedLead CHANGE `level` listing_level INT(11) DEFAULT NULL, CHANGE field leads INT(11) DEFAULT NULL');
    }
}
