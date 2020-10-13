<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaEvent\DataFixtures\ORM\Common;

use ArcaSolutions\EventBundle\Entity\EventLevelField;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadEventLevelFieldData
 */
class LoadEventLevelFieldData extends AbstractFixture implements OrderedFixtureInterface
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
                'field' => 'facebook_page',
            ],
            [
                'level' => 10,
                'field' => 'twitter_page',
            ],
            [
                'level' => 10,
                'field' => 'instagram_page',
            ],
            [
                'level' => 30,
                'field' => 'facebook_page',
            ],
            [
                'level' => 30,
                'field' => 'twitter_page',
            ],
        ];

        foreach ($standardInserts as $listingLevelFieldInsert) {

            if (!$manager->getRepository('EventBundle:EventLevelField')->findOneBy([
                'level' => $listingLevelFieldInsert['level'],
                'field' => $listingLevelFieldInsert['field'],
            ])) {

                $listingLevelField = new EventLevelField();

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
