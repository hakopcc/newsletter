<?php

namespace ArcaSolutions\CoreBundle\DataFixtures\ORM;


use ArcaSolutions\CoreBundle\Entity\Consent;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadConsentData
 * @package ArcaSolutions\CoreBundle\DataFixtures\ORM
 */
class LoadConsentData extends AbstractFixture implements OrderedFixtureInterface
{

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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            ['signup'],
            ['payment'],
            ['review'],
            ['contactUs'],
            ['lead'],
            ['newsletter']
        ];

        $repository = $manager->getRepository('CoreBundle:Consent');

        foreach ($standardInserts as list($value)) {

            $query = $repository->findOneBy([
                'value' => $value,
            ]);

            $consent = new Consent();

            /* checks if the consent already exist so they can be updated or added */
            if ($query) {
                $consent = $query;
            }

            $consent->setValue($value);

            $manager->persist($consent);
        }

        $manager->flush();
    }
}
