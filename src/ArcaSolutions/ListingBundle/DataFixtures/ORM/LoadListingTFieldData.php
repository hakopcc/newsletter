<?php

namespace ArcaSolutions\ListingBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingTFieldData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingTFieldData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var ListingTemplate $listingTemplateReference */
        if($this->hasReference('TEMPLATE_' . ListingTemplate::LISTING)) {
            $listingTemplateReference = $this->getReference('TEMPLATE_' . ListingTemplate::LISTING);
        } else {
            $listingTemplateReference = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->findOneBy([
                'title' => ListingTemplate::LISTING
            ]);
        }

        if(!empty($listingTemplateReference)) {
            $this->container->get('listingtemplatefield.service')->createDefaultListingTemplateFields($listingTemplateReference, $this->referenceRepository);
        }
    }


    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
