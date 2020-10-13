<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\DataFixtures\ORM\Common;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\DefaultListingTemplateFields;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingTemplateFieldData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingTemplateFieldData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        /* These are the standard data of the system */
        $standardInserts = array(
            array(
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => DefaultListingTemplateFields::ADVANCED_REVIEW
            )
        );
        $repository = $manager->getRepository('ListingBundle:ListingTField');
        $needFlush = false;
        foreach ($standardInserts as $standardInsert) {
            /** @var ListingTemplate[] $listingTemplates */
            $listingTemplates = $manager->getRepository('ListingBundle:ListingTemplate')->findAll();
            foreach($listingTemplates as $listingTemplate) {
                $listingTFieldCount = $repository->count([
                    'label' => $standardInsert['field'],
                    'fieldType' => $standardInsert['fieldType'],
                    'listingTemplate' => $listingTemplate
                ]);

                if (empty($listingTFieldCount)) {
                    $templateField = new ListingTField();
                    $templateField->setListingTemplate($listingTemplate);
                    $templateField->setLabel($standardInsert['field']);
                    $templateField->setFieldType($standardInsert['fieldType']);
                    $manager->persist($templateField);
                    $needFlush = true;
                }
            }
        }
        if($needFlush) {
            $manager->flush();
        }
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
