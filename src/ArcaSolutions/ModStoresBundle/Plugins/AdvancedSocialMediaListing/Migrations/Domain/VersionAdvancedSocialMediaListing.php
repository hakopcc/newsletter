<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdvancedSocialMediaListing extends AbstractMigration
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

        $this->addSql('CREATE TABLE IF NOT EXISTS Listing_SocialMedia (id INT AUTO_INCREMENT NOT NULL, listing_id INT DEFAULT NULL, import_id INT DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, temp VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4EDF0DE6D4619D1A (listing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('l.id, l.social_network')
            ->from('Listing', 'l')
            ->where('l.social_network <> :socialNetwork')
            ->setParameter('socialNetwork', '');

        $listings = $queryBuilder->execute()->fetchAll();

        foreach ($listings as $listing) {
            $socialNetwork = json_decode($listing['social_network'], true);
            $this->connection->insert('Listing_SocialMedia',
                [
                    'listing_id' => $listing['id'],
                    'facebook'   => !empty($socialNetwork['facebook']) ? $socialNetwork['facebook'] : '',
                    'twitter'    => !empty($socialNetwork['twitter']) ? $socialNetwork['twitter'] : '',
                    'temp'       => 'n',
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

        $this->addSql('DROP TABLE Listing_SocialMedia');
    }
}
