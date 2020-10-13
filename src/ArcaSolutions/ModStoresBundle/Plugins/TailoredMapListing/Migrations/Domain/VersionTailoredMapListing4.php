<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionTailoredMapListing4 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        if(!empty($this->container)) return $this->container;
        if(!empty($this->version)) {
            $versionConfiguration = $this->version->getConfiguration();
            if(!empty($versionConfiguration)) {
                return $versionConfiguration->getContainer();
            }
        }
        return null;
    }

    /**
     * @param Schema $schema
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function up(Schema $schema)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('w.id')
            ->from('Widget', 'w')
            ->where('w.title = :title')
            ->orderBy('w.id', 'DESC')
            ->setParameter(':title', 'Tailored Map');

        $tailoredMapId = $queryBuilder->execute()->fetch();

        if(!empty($tailoredMapId)) {

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->select('w.id')
                ->from('Widget', 'w')
                ->where('w.title = :title')
                ->setParameter(':title', 'Search box with Tailored Map');

            $tailoredMapSearchs = $queryBuilder->execute()->fetchAll();

            if(!empty($tailoredMapSearchs)) {
                $container = $this->getContainer();
                if(!empty($container)) {
                    $em = $container->get('doctrine.orm.entity_manager');

                    $tailoredMap = $container->get('doctrine')->getRepository('WysiwygBundle:Widget')->find($tailoredMapId['id']);

                    foreach ($tailoredMapSearchs as $tailoredMapSearch) {
                        $pageWidgets = $container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy([
                            'widgetId' => $tailoredMapSearch['id']
                        ]);

                        foreach ($pageWidgets as $pageWidget) {
                            $pageWidget->setWidget($tailoredMap);
                            $em->persist($pageWidget);
                        }

                        $queryBuilder
                            ->delete('Widget_Theme')
                            ->where('widget_id = :widgetId')
                            ->setParameter(':widgetId', $tailoredMapSearch['id'])
                            ->execute();

                        $queryBuilder
                            ->delete('Widget_PageType')
                            ->where('widget_id = :widgetId')
                            ->setParameter(':widgetId', $tailoredMapSearch['id'])
                            ->execute();

                        $widgetTailoredMapSearch = $container->get('doctrine')->getRepository('WysiwygBundle:Widget')->find($tailoredMapSearch['id']);

                        !empty($widgetTailoredMapSearch) and $em->remove($widgetTailoredMapSearch);
                    }
                    $em->flush();
                }
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
