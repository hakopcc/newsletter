<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\DataFixtures\ORM\Common;

use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use ArcaSolutions\ListingBundle\Entity\ListingLevelField;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\DefaultListingLevelFields;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\DefaultListingTemplateFields;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadListingLevelFieldData
 */
class LoadListingLevelFieldData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $standardInserts = [
            ListingLevel::DIAMOND_LEVEL => [
                [
                    'field'         => DefaultListingLevelFields::ADVANCED_REVIEW,
                    'quantity'      => NULL,
                    'templateField' => DefaultListingTemplateFields::ADVANCED_REVIEW
                ]
            ],
            ListingLevel::GOLD_LEVEL => [
                [
                    'field'         => DefaultListingLevelFields::ADVANCED_REVIEW,
                    'quantity'      => NULL,
                    'templateField' => DefaultListingTemplateFields::ADVANCED_REVIEW
                ],
            ],
        ];

        $repository = $manager->getRepository('ListingBundle:ListingLevelField');

        foreach ($standardInserts as $level => $listingLevelFieldInserts) {
            /** @var ListingLevel $listingLevel */
            $listingLevel = $manager->getRepository('ListingBundle:ListingLevel')->find($level);
            foreach($listingLevelFieldInserts as $listingLevelFieldInsert) {
                $query = $repository->findOneBy([
                    'level' => $level,
                    'field' => $listingLevelFieldInsert['field'],
                ]);

                $listingLevelField = new ListingLevelField();

                /* checks if the listingLevelField already exist so they can be updated or added */
                if (!$query) {
                    if($this->hasReference('FIELD_' . $listingLevelFieldInsert['templateField'])) {
                        /** @var ListingTField $templateField */
                        $templateField = $this->getReference('FIELD_' . $listingLevelFieldInsert['templateField']);
                    } else {
                        $templateField = $manager->getRepository('ListingBundle:ListingTField')->findOneBy([
                            'label'     => $listingLevelFieldInsert['templateField'],
                            'fieldType' => 'default'
                        ]);
                    }

                    $listingLevelField->setListingLevel($listingLevel);
                    $listingLevelField->setField($listingLevelFieldInsert['field']);
                    $listingLevelField->setListingTField($templateField);
                    !empty($listingLevelFieldInsert['quantity']) and $listingLevelField->setQuantity($listingLevelFieldInsert['quantity']);

                    $manager->persist($listingLevelField);
                }
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
        return 1;
    }
}
