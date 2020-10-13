<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\WidgetPageType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadWidgetPageTypeData
 *
 * This class is responsible for inserting at the Database the standard Widget_PageType of the system
 *
 */
class LoadWidgetPageTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $widgetRepository = $manager->getRepository('WysiwygBundle:Widget');
        $pageTypeRepository = $manager->getRepository('WysiwygBundle:PageType');

        $standardWidgetPageTypes = [
            [
                'widget'   => $this->hasReference('Forum Detail') ?
                    $this->getReference('Forum Detail') :
                    $widgetRepository->findOneBy(['title' => 'Forum Detail']),
                'pageType' => $this->hasReference('TYPE_Forum Detail') ?
                    $this->getReference('TYPE_Forum Detail') :
                    $pageTypeRepository->findOneBy(['title' => 'Forum Detail']),
            ],
        ];

        foreach ($standardWidgetPageTypes as $sWidgetPageType) {

            $widgetPageType = new WidgetPageType();

            $query = $manager->getRepository('WysiwygBundle:WidgetPageType')->findOneBy([
                'pageType' => $sWidgetPageType['pageType'],
                'widget'   => $sWidgetPageType['widget'],
            ]);

            $query and $widgetPageType = $query;

            $widgetPageType->setWidget($sWidgetPageType['widget']);
            $widgetPageType->setPageType($sWidgetPageType['pageType']);

            $manager->persist($widgetPageType);
            $manager->flush();
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
        return 4;
    }
}
