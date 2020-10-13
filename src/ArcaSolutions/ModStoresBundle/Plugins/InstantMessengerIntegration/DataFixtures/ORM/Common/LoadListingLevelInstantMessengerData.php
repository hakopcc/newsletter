<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\DataFixtures\ORM\Common;

use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\ListingLevelInstantMessenger;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadListingLevelInstantMessengerData.
 */
class LoadListingLevelInstantMessengerData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $listingLevelRepository = $manager->getRepository('ListingBundle:ListingLevel');
        if (!empty($listingLevelRepository)) {
            $listingLevels = $listingLevelRepository->findAll();
            if (!empty($listingLevels)) {
                $listingLevelInstantMessengerRepository = $manager->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                if (!empty($listingLevelInstantMessengerRepository)) {
                    $needToFlush = false;
                    foreach ($listingLevels as $listingLevel) {
                        $listingLevelValue = $listingLevel->getValue();
                        if (!empty($listingLevelValue)) {
                            $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy(['level' => $listingLevelValue]);
                            if (empty($listingLevelInstantMessenger)) {
                                $newListingLevelInstantMessenger = new ListingLevelInstantMessenger();
                                $newListingLevelInstantMessenger->setLevel($listingLevel);
                                $newListingLevelInstantMessenger->setInstantMessenger('n');
                                $manager->persist($newListingLevelInstantMessenger);
                                if (!$needToFlush) {
                                    $needToFlush = true;
                                }
                            }
                        }
                        unset($listingLevelValue);
                    }
                    if ($needToFlush) {
                        $manager->flush();
                    }
                    unset($needToFlush);
                }
                unset($listingLevelInstantMessengerRepository);
            }
            unset($listingLevels);
        }
        unset($listingLevelRepository);
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
