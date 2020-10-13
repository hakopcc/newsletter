<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\GeoTargetedBanner\DataFixtures\ORM\Common;

use ArcaSolutions\WebBundle\Entity\Setting;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadSettingData
 */
class LoadSettingData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $standardInserts = [
            [
                'name'  => 'miles_distance',
                'value' => '25000',
            ],
        ];

        foreach ($standardInserts as $settingInsert) {

            if (!$manager->getRepository('WebBundle:Setting')->findOneBy([
                'name' => $settingInsert['name'],
            ])) {

                $setting = new Setting();

                $setting->setName($settingInsert['name']);
                $setting->setValue($settingInsert['value']);

                $manager->persist($setting);

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

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
