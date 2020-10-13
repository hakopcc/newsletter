<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdvancedSocialMediaEvent2 extends AbstractMigration
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

        if ($schema->hasTable('Event_SocialMedia')) {
            if ($schema->getTable('Event_SocialMedia')->hasIndex('uniq_4edf0de6d4619d1a')) {
                //Clean nonexistent relationship to add Foreign Key.
                $this->addSql('DELETE FROM Event_SocialMedia WHERE event_id NOT IN (SELECT id FROM Event)');
                $this->addSql('DROP INDEX uniq_4edf0de6d4619d1a ON Event_SocialMedia');
            }
            $this->addSql('ALTER TABLE Event_SocialMedia ADD CONSTRAINT FK_BA6F43DA71F7E88B FOREIGN KEY (event_id) REFERENCES Event (id)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_BA6F43DA71F7E88B ON Event_SocialMedia (event_id)');
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

        $this->addSql('ALTER TABLE Event_SocialMedia DROP FOREIGN KEY FK_BA6F43DA71F7E88B');
        $this->addSql('DROP INDEX uniq_ba6f43da71f7e88b ON Event_SocialMedia');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_4EDF0DE6D4619D1A ON Event_SocialMedia (event_id)');
        $this->addSql('ALTER TABLE Event_SocialMedia ADD CONSTRAINT FK_BA6F43DA71F7E88B FOREIGN KEY (event_id) REFERENCES Event (id)');
    }
}
