<?php

namespace Application\Migrations;

use AutoEmbed;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionAdditionalVideosListing3 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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

        //Clean nonexistent relationship to add Foreign Key.
        $this->addSql('DELETE FROM Listing_Videos WHERE listing_id NOT IN (SELECT id FROM Listing)');

        $this->addSql('ALTER TABLE Listing_Videos ADD video_image_url LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX listing_id ON Listing_Videos');
        $this->addSql('ALTER TABLE Listing_Videos ADD CONSTRAINT FK_79A380FD4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
        $this->addSql('CREATE INDEX IDX_79A380FD4619D1A ON Listing_Videos (listing_id)');
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     */
    public function postUp(Schema $schema)
    {
        include_once __DIR__ . '/../../../web/src/classes/AutoEmbed-1.8/AutoEmbed.class.php';

        $AE = new AutoEmbed();

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('lv.id, lv.video_url')
            ->from('Listing_Videos', 'lv');

        $listingVideos = $queryBuilder->execute()->fetchAll();

        foreach ($listingVideos as $listingVideo) {

            $videoURL = str_replace('https://', 'http://', $listingVideo['video_url']);

            // load the embed source from a remote url
            if (!$AE->parseUrl($videoURL)) {
                $error = true;
            }
            $AE->setWidth(380);
            $imageUrl = str_replace('http://', 'https://', $AE->getImageURL());

            $this->connection->update('Listing_Videos', [
                'video_image_url' => $imageUrl
            ], ['id' => $listingVideo['id']]);
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

        $this->addSql('ALTER TABLE Listing_Videos DROP FOREIGN KEY FK_79A380FD4619D1A');
        $this->addSql('ALTER TABLE Listing_Videos DROP video_image_url');
        $this->addSql('DROP INDEX idx_79a380fd4619d1a ON Listing_Videos');
        $this->addSql('CREATE INDEX listing_id ON Listing_Videos (listing_id)');
        $this->addSql('ALTER TABLE Listing_Videos ADD CONSTRAINT FK_79A380FD4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
    }
}
