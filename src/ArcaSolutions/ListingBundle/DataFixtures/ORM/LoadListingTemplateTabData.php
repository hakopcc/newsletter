<?php

namespace ArcaSolutions\ListingBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingTemplateTabData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingTemplateTabData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $trans = $this->container->get('translator');

        /* These are the standard data of the system */
        $standardInserts = [
            ListingTemplate::LISTING => [
                [
                    'title' => ListingTemplateTab::OVERVIEW,
                    'order' => 0
                ],
                [
                    'title' => ListingTemplateTab::PHOTOS,
                    'order' => 1
                ],
                [
                    'title' => ListingTemplateTab::REVIEWS,
                    'order' => 2
                ],
                [
                    'title' => ListingTemplateTab::DEALS,
                    'order' => 3
                ],
                [
                    'title' => ListingTemplateTab::CLASSIFIEDS,
                    'order' => 4
                ]
            ],
        ];

        $repository = $manager->getRepository('ListingBundle:ListingTemplateTab');

        foreach ($standardInserts as $listingTemplate => $listingTabs) {
            /** @var ListingTemplate $listingTemplateReference */
            $listingTemplateReference = $this->getReference('TEMPLATE_' . $listingTemplate);

            if (!empty($repository->findBy(['listingTemplateId' => $listingTemplateReference->getId()]))) {
                continue;
            }

            foreach($listingTabs as $listingTab) {
                $query = $repository->findOneBy([
                    'title' => $listingTab['title'],
                ]);

                if (empty($query)) {
                    $listingTemplateTab = new ListingTemplateTab();
                    $listingTemplateTab->setListingTemplate($listingTemplateReference);
                    $listingTemplateTab->setTitle(/** @Ignore */$trans->trans($listingTab['title'], [], 'administrator'));
                    $listingTemplateTab->setOrder($listingTab['order']);
                } else {
                    $listingTemplateTab = $query;
                }

                $manager->persist($listingTemplateTab);

                $this->addReference('TAB_' . $listingTab['title'], $listingTemplateTab);
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
        return 2;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
