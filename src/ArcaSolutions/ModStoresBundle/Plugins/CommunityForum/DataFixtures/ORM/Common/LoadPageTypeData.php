<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadPageTypeData
 *
 */
class LoadPageTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $standardPageTypes = [
            [
                'title' => 'Forum Homepage',
            ],
            [
                'title' => 'Forum Detail',
            ],
        ];

        foreach ($standardPageTypes as $standardPageType) {

            $pageType = new PageType();

            $query = $manager->getRepository('WysiwygBundle:PageType')->findOneBy([
                'title' => $standardPageType['title'],
            ]);

            $query and $pageType = $query;


            $pageType->setTitle($standardPageType['title']);

            $manager->persist($pageType);
            $manager->flush();

            $this->addReference('TYPE_'.$pageType->getTitle(), $pageType);
        }
    }

    /**
     * the order in which fixtures will be loaded
     * the lower the number, the sooner that this fixture is loaded
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
