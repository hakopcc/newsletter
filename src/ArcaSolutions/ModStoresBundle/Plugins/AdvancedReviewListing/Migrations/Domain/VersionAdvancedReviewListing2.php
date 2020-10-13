<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdvancedReviewListing2 extends AbstractMigration
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

        if ($schema->hasTable('Review_RatingType')) {
            if ($schema->getTable('Review_RatingType')->hasForeignKey('FK_E62D2F21A32EFC6')) {
                $this->addSql('ALTER TABLE Review_RatingType DROP FOREIGN KEY FK_E62D2F21A32EFC6');
            }
        }
        if ($schema->hasTable('Review_RatingType')) {
            if ($schema->getTable('Review_RatingType')->hasIndex('idx_e62d2f21a32efc6')) {
                $this->addSql('DROP INDEX idx_e62d2f21a32efc6 ON Review_RatingType');
            }
        }
        if ($schema->hasTable('Review_RatingType')) {
            if (!$schema->getTable('Review_RatingType')->hasIndex('IDX_6E302C33A32EFC6')) {
                $this->addSql('CREATE INDEX IDX_6E302C33A32EFC6 ON Review_RatingType (rating_id)');
            }
        }
        if ($schema->hasTable('Review_RatingType')) {
            if (!$schema->getTable('Review_RatingType')->hasForeignKey('FK_E62D2F21A32EFC6')) {
                $this->addSql('ALTER TABLE Review_RatingType ADD CONSTRAINT FK_E62D2F21A32EFC6 FOREIGN KEY (rating_id) REFERENCES RatingType (id) ON DELETE CASCADE');
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('Review_RatingType')) {
            if ($schema->getTable('Review_RatingType')->hasForeignKey('FK_6E302C33A32EFC6')) {
                $this->addSql('ALTER TABLE Review_RatingType DROP FOREIGN KEY FK_6E302C33A32EFC6');
            }
        }
        if ($schema->hasTable('Review_RatingType')) {
            if ($schema->getTable('Review_RatingType')->hasIndex('idx_6e302c33a32efc6')) {
                $this->addSql('DROP INDEX idx_6e302c33a32efc6 ON Review_RatingType');
            }
        }
        if ($schema->hasTable('Review_RatingType')) {
            if (!$schema->getTable('Review_RatingType')->hasIndex('IDX_E62D2F21A32EFC6')) {
                $this->addSql('CREATE INDEX IDX_E62D2F21A32EFC6 ON Review_RatingType (rating_id)');
            }
        }
        if ($schema->hasTable('Review_RatingType')) {
            if (!$schema->getTable('Review_RatingType')->hasForeignKey('FK_6E302C33A32EFC6')) {
                $this->addSql('ALTER TABLE Review_RatingType ADD CONSTRAINT FK_6E302C33A32EFC6 FOREIGN KEY (rating_id) REFERENCES RatingType (id) ON DELETE CASCADE');
            }
        }
    }
}
