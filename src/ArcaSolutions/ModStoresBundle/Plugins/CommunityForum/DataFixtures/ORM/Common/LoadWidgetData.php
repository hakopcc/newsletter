<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadWidgetData
 *
 * This class is responsible for inserting at the DataBase the standard widgets of the system
 *
 */
class LoadWidgetData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $standardWidgets = [
            [
                'title'    => 'Forum Detail',
                'twigFile' => '/forum/forum-detail.html.twig',
                'type'     => 'forum',
                'content'  => [
                    'labelCategories'     => 'Categories',
                    'labelPopularQuestions'   => 'Popular topics'
                ],
                'modal'    => '',
            ],
            [
                'title'    => 'Horizontal Question Bar',
                'twigFile' => '/forum/horizontal-question-bar.html.twig',
                'type'     => 'forum',
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => 'Two columns recent questions',
                'twigFile' => '/forum/two-columns-recent-questions.html.twig',
                'type'     => 'forum',
                'content'  => [
                    'labelCategories'     => 'Categories',
                    'labelPopularQuestions'   => 'Popular topics',
                    'hasDesign'           => 'false',
                    'backgroundColor'     => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
        ];

        foreach ($standardWidgets as $sWidget) {

            $widget = new Widget();

            $query = $manager->getRepository('WysiwygBundle:Widget')->findOneBy([
                'twigFile' => $sWidget['twigFile'],
                'title'    => $sWidget['title'],
            ]);

            $query and $widget = $query;

            $widget->setTitle($sWidget['title']);
            $widget->setTwigFile($sWidget['twigFile']);
            $widget->setType($sWidget['type']);
            $widget->setContent(json_encode($sWidget['content']));
            $widget->setModal($sWidget['modal']);

            $manager->persist($widget);
            $manager->flush();

            $this->addReference($widget->getTitle(), $widget);
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
