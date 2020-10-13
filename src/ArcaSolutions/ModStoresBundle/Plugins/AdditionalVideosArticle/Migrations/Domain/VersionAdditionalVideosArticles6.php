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
class VersionAdditionalVideosArticles6 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('ALTER TABLE Article_Videos ADD video_image_url LONGTEXT DEFAULT NULL');
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
            ->select('av.id, av.video_url')
            ->from('Article_Videos', 'av');

        $articleVideos = $queryBuilder->execute()->fetchAll();

        foreach ($articleVideos as $articleVideo) {
            $videoURL = str_replace('https://', 'http://', $articleVideo['video_url']);

            // load the embed source from a remote url
            if (!$AE->parseUrl($videoURL)) {
                $error = true;
            }
            $AE->setWidth(380);
            $imageUrl = str_replace('http://', 'https://', $AE->getImageURL());

            $this->connection->update('Article_Videos', [
                'video_image_url' => $imageUrl
            ], ['id' => $articleVideo['id']]);
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

        $this->addSql('ALTER TABLE Article_Videos DROP video_image_url');
    }
}
