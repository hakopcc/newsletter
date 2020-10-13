<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPageData
 *
 * This class is responsible for inserting at the Database the standard Pages of the system
 * For example: Home Page, Listing Home, Results.
 *
 */
class LoadPageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $trans = $this->container->get('translator');

        $standardPages = [
            [
                'title'     => $trans->trans('Forum Homepage', [], 'widgets', 'en'),
                'url'       => $this->container->getParameter('alias_forum_module'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => false,
                'customTag' => '',
                'pageType'  => $this->getReference('TYPE_Forum Homepage'),
            ],
            [
                'title'     => $trans->trans('Forum Detail', [], 'widgets', 'en'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference('TYPE_Forum Detail'),
            ],
        ];

        foreach ($standardPages as $standardPage) {

            $page = new Page();

            $query = $manager->getRepository('WysiwygBundle:Page')->getPageByType($standardPage['pageType']->getTitle());

            $query and $page = $query;

            $page->setTitle($standardPage['title']);
            $page->setUrl($standardPage['url']);
            $page->setMetaDescription($standardPage['metaDesc']);
            $page->setMetaKey($standardPage['metaKey']);
            $page->setSitemap($standardPage['sitemap']);
            $page->setCustomTag($standardPage['customTag']);
            $page->setPageType($standardPage['pageType']);

            $manager->persist($page);
            $manager->flush();

            $this->addReference($page->getPageType()->getTitle().'_REFERENCE', $page);
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
        return 2;
    }
}
