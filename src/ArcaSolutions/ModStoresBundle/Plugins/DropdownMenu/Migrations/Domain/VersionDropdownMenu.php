<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionDropdownMenu extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');
        if (!$schema->hasTable('Setting_Navigation_DropdownMenu')) {
            $this->addSql('CREATE TABLE IF NOT EXISTS Setting_Navigation_DropdownMenu (id INT AUTO_INCREMENT NOT NULL, parent_menu INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('Setting_Navigation_DropdownMenu')) {
            $this->addSql('DROP TABLE Setting_Navigation_DropdownMenu');
        }
    }
}
