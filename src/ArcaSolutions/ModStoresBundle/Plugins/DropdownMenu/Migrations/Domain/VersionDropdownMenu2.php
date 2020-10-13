<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionDropdownMenu2 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('Setting_Navigation_DropdownMenu')) {
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('id')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE id id INT(11) NOT NULL');
            }
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('parent_menu')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE parent_menu parent_menu INT(11) NULL');
            }
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
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('id')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE id id INT(11) NOT NULL AUTO_INCREMENT');
            }
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('parent_menu')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE parent_menu parent_menu INT(11) NOT NULL');
            }
        }
    }
}
