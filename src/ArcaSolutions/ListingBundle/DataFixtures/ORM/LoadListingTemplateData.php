<?php

namespace ArcaSolutions\ListingBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingTemplateData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingTemplateData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $trans = $this->container->get('translator');

        /* These are the standard data of the system */
        $standardInserts = [
            [
                'title' => ListingTemplate::LISTING
            ],
        ];

        $repository = $manager->getRepository('ListingBundle:ListingTemplate');

        foreach ($standardInserts as $sListingTemplate) {
            $query = $repository->findOneBy([
                'title' => $sListingTemplate['title'],
            ]);

            if (empty($query)) {
                $listingTemplate = new ListingTemplate();

                $listingTemplate->setUpdated(new \DateTime());
                $listingTemplate->setEntered(new \DateTime());
                $listingTemplate->setTitle(/** @Ignore */$trans->trans($sListingTemplate['title'], [], 'widgets'));
                $listingTemplate->setSummaryTemplate(1);

                $manager->persist($listingTemplate);
                $manager->flush();
            } else {
                $listingTemplate = $query;
            }

            $this->addReference('TEMPLATE_' . $sListingTemplate['title'], $listingTemplate);
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
        return 1;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
