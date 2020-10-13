<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionDropdownMenu4 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     * @throws DBALException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('Setting_Navigation_DropdownMenu')) {
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('parent_menu')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE parent_menu parent_menu INT DEFAULT NULL');
            }
        }
    }

    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     * @throws DBALException
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('Setting_Navigation_DropdownMenu')) {
            if ($schema->getTable('Setting_Navigation_DropdownMenu')->hasColumn('parent_menu')) {
                $this->addSql('ALTER TABLE Setting_Navigation_DropdownMenu CHANGE parent_menu parent_menu INT NOT NULL');
            }
        }
    }
}
