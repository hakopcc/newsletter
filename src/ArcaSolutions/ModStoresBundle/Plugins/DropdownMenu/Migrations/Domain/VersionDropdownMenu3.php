<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionDropdownMenu3 extends AbstractMigration
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
        if ($schema->hasTable('Setting_Navigation_DropdownMenu')) {
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('id')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE id id INT NOT NULL');
            }
        }
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
        if ($schema->hasTable('Setting_Navigation_DropdownMenu')) {
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('id')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE id id INT NOT NULL, CHANGE parent_menu parent_menu INT DEFAULT NULL');
            }
        }
    }
}
