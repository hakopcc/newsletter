<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdvancedReviewListing extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if (!$schema->hasTable('Review_RatingType')) {
            $this->addSql('CREATE TABLE IF NOT EXISTS Review_RatingType (id INT AUTO_INCREMENT NOT NULL, rating_id INT DEFAULT NULL, review_id INT NOT NULL, value INT NOT NULL, INDEX IDX_E62D2F21A32EFC6 (rating_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        }
        if (!$schema->hasTable('RatingType')) {
            $this->addSql('CREATE TABLE RatingType (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(255) DEFAULT NULL, listingtemplate_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        }
        if ($schema->hasTable('Review_RatingType')) {
            if (!$schema->getTable('Review_RatingType')->hasForeignKey('FK_E62D2F21A32EFC6')) {
                $this->addSql('ALTER TABLE Review_RatingType ADD CONSTRAINT FK_E62D2F21A32EFC6 FOREIGN KEY (rating_id) REFERENCES RatingType (id) ON DELETE CASCADE');
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',            'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('ReviewAdv')) {
            $this->addSql('DROP TABLE ReviewAdv');
        }
        if ($schema->hasTable('ReviewAdvItem')) {
            $this->addSql('DROP TABLE ReviewAdvItem');
        }
        if ($schema->hasTable('ReviewAdvRating')) {
            $this->addSql('DROP TABLE ReviewAdvRating');
        }
    }
}
