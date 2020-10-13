<?php

namespace ArcaSolutions\WysiwygBundle\DataFixtures\ORM;

use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingWidgetData
 * @package ArcaSolutions\WysiwygBundle\DataFixtures\ORM
 */
class LoadListingWidgetData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardWidgets = $this->container->get('listingwidget.service')->getDefaultListingWidgets();

        $repository = $manager->getRepository('WysiwygBundle:ListingWidget');

        foreach ($standardWidgets as $sectionListingWidget) {
            foreach ($sectionListingWidget as $sListingWidget) {
                $query = $repository->findOneBy([
                    'twigFile' => $sListingWidget['twigFile'],
                    'title'    => $sListingWidget['title'],
                    'section'  => $sListingWidget['section']
                ]);

                $listingWidget = new ListingWidget();
                /* checks if the widget already exist so they can be updated or added */
                if ($query) {
                    $listingWidget = $query;
                }

                $listingWidget->setTitle($sListingWidget['title']);
                $listingWidget->setTwigFile($sListingWidget['twigFile']);
                $listingWidget->setType($sListingWidget['type']);
                $listingWidget->setContent(json_encode($sListingWidget['content']));
                $listingWidget->setModal($sListingWidget['modal']);
                $listingWidget->setSection($sListingWidget['section']);

                $manager->persist($listingWidget);
                $manager->flush();

                $this->addReference('LISTING_' . $sListingWidget['section'] . '_' . $listingWidget->getTitle(), $listingWidget);
            }
        }

        $manager->flush();
    }


    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
