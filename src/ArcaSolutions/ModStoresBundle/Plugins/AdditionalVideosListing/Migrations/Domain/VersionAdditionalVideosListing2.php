<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalVideosListing2 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing_Videos CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE listing_id listing_id INT DEFAULT NULL');
        $this->addSql('RENAME TABLE ListingLevel_Videos TO ListingLevel_FieldVideos');
        $this->addSql('ALTER TABLE ListingLevel_FieldVideos DROP COLUMN module;');
        $this->addSql('ALTER TABLE ListingLevel_FieldVideos ADD id INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id);');
        $this->addSql('ALTER TABLE ListingLevel_FieldVideos CHANGE level_id `level` INT(11) NOT NULL, CHANGE videos field INT(11) NOT NULL');
        $this->addSql('ALTER TABLE ListingLevel_FieldVideos DROP INDEX level_id_module');
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('l.id, l.video_url', 'l.video_snippet', 'l.video_description')
            ->from('Listing', 'l')
            ->where('l.video_url <> :video_url')
            ->orWhere('l.video_snippet <> :video_snippet')
            ->setParameter('video_url', '')
            ->setParameter('video_snippet', '');

        $listings = $queryBuilder->execute()->fetchAll();

        foreach ($listings as $listing) {
            $this->connection->insert('Listing_Videos',
                [
                    'listing_id'        => $listing['id'],
                    'video_url'         => $listing['video_url'],
                    'video_snippet'     => $listing['video_snippet'],
                    'video_description' => $listing['video_description'],
                ]);
        }
    }

    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX listing_id ON Listing_Videos (listing_id)');
        $this->addSql('RENAME TABLE ListingLevel_FieldVideos TO ListingLevel_Videos');
        $this->addSql('ALTER TABLE ListingLevel_Videos ADD module VARCHAR(50) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE ListingLevel_Videos DROP id');
        $this->addSql('ALTER TABLE ListingLevel_FieldVideos CHANGE `level` level_id INT(11) UNSIGNED DEFAULT NULL, CHANGE field videos TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE ListingLevel_Videos ADD UNIQUE level_id_module (`level_id`, `module`)');
    }
}
