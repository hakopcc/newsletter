<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Error;

/**
 * Class LoadPageWidgetData
 *
 * This class is responsible for inserting at the DataBase the standard PageWidgets of the system
 * The table PageWidgets has the information of which widgets a page has and in which order they are allocated.
 *
 */
class LoadPageWidgetData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Twig_Error
     */
    public function load(ObjectManager $manager)
    {
        foreach ($manager->getRepository('WysiwygBundle:Theme')->findAll() as $theme) {

            $this->container->get('theme.service')->setTheme($theme->getTitle());

            $pagesDefault = [
                'Forum Homepage' => $this->container->get('pagewidget.service')->getAllPageDefaultWidgets()['Forum Homepage'],
                'Forum Detail'   => $this->container->get('pagewidget.service')->getAllPageDefaultWidgets()['Forum Detail'],
            ];

            foreach ($pagesDefault as $pageType => $pageDefaults) {
                $page = $this->getReference($pageType.'_REFERENCE');

                if ($manager->getRepository('WysiwygBundle:PageWidget')->hasWidgetOnPage($page->getId(), $theme->getId())) {
                    continue;
                }

                foreach ($pageDefaults as $i => $pageDefault) {
                    $pageDefaultWidgetTitle = $pageDefault;
                    if(!is_string($pageDefault) && is_array($pageDefault) && !empty($pageDefault)){
                        $pageDefaultKeys = array_keys($pageDefault);
                        $pageDefaultWidgetTitle = $pageDefaultKeys[0];
                    }
                    $widget = $this->hasReference($pageDefaultWidgetTitle) ?
                        $this->getReference($pageDefaultWidgetTitle) :
                        $manager->getRepository('WysiwygBundle:Widget')->findOneBy(['title' => $pageDefaultWidgetTitle]);
                    if(empty($widget)){
                        echo "Cannot find widget with title ". $pageDefaultWidgetTitle . PHP_EOL;
                        unset($pageDefaultWidgetTitle, $widget);
                        continue;
                    }
		    
		    $content = null;
                    if (is_array($pageDefault)) {
                        $content = json_encode(current($pageDefault)['content']);
                    }

                    $pageWidget = new PageWidget();

                    $pageWidget->setTheme($theme);
                    $pageWidget->setPage($page);

                    $pageWidget->setWidget($widget);

                    if (!empty($content)) {
                        $widgetContent = $content;
                    } else {
                        $widgetContent = $widget !== null ? $widget->getContent() : '';
                    }

                    $pageWidget->setContent($widgetContent);
                    $pageWidget->setOrder($i + 1);

                    $manager->persist($pageWidget);
                    $manager->flush();
                }
            }
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
        return 3;
    }
}
