<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\DataFixtures\ORM\Common;

use ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\Entity\ListingLevelFieldArticles;
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
            [
                'level' => 10,
                'field' => 10,
            ],
            [
                'level' => 30,
                'field' => 5,
            ],
            [
                'level' => 50,
                'field' => 3,
            ],
            [
                'level' => 70,
                'field' => 0,
            ],
        ];

        foreach ($standardInserts as $listingLevelFieldInsert) {

            if (!$manager->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy([
                'level' => $listingLevelFieldInsert['level'],
            ])) {

                $listingLevelField = new ListingLevelFieldArticles();

                $listingLevelField->setLevel($listingLevelFieldInsert['level']);
                $listingLevelField->setField($listingLevelFieldInsert['field']);

                $manager->persist($listingLevelField);

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