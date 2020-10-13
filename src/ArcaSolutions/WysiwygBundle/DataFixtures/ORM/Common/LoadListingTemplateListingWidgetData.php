<?php

namespace ArcaSolutions\WysiwygBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingTemplateListingWidgetData
 * @package ArcaSolutions\WysiwygBundle\DataFixtures\ORM
 */
class LoadListingTemplateListingWidgetData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $standardWidgets = $this->container->get('listingtemplate.listingwidget.service')->getListingDefaultWidgets();

        $repository = $manager->getRepository('WysiwygBundle:ListingTemplateListingWidget');

        foreach ($standardWidgets as $listingTemplate => $listingTabs) {
            /** @var ListingTemplate $listingTemplateReference */
            if($this->hasReference('TEMPLATE_' . $listingTemplate)) {
                $listingTemplateReference = $this->getReference('TEMPLATE_' . $listingTemplate);
            } else {
                $listingTemplateReference = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->findOneBy([
                    'title' => /** @Ignore */$trans->trans($listingTemplate, [], 'widgets')
                ]);
            }

            if (!empty($repository->findBy(['listingTemplateId' => $listingTemplateReference->getId()]))) {
                continue;
            }

            if(!empty($listingTabs[ListingWidget::HEADER_SECTION])) {
                /** @var ListingWidget $listingWidget */
                if($this->hasReference('LISTING_header_' . $listingTabs[ListingWidget::HEADER_SECTION])) {
                    $listingWidget = $this->getReference('LISTING_header_' . $listingTabs[ListingWidget::HEADER_SECTION]);
                } else {
                    $listingWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findOneBy([
                        'title' => $listingTabs[ListingWidget::HEADER_SECTION]
                    ]);
                }

                $listingTemplateListingWidget = new ListingTemplateListingWidget();
                $listingTemplateListingWidget->setListingTemplate($listingTemplateReference);
                $listingTemplateListingWidget->setListingWidget($listingWidget);
                $listingTemplateListingWidget->setOrder(0);
                $listingTemplateListingWidget->setContent($listingWidget->getContent());

                $manager->persist($listingTemplateListingWidget);
            }

            foreach ($listingTabs['tabs'] as $tab => $listingSections) {

                /** @var ListingTemplateTab $listingTemplateTab */
                if($this->hasReference('TAB_' . $tab)) {
                    $listingTemplateTab = $this->getReference('TAB_' . $tab);
                } else {
                    $listingTemplateTab = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->findOneBy([
                        'title' => /** @Ignore */$trans->trans($tab, [], 'administrator')
                    ]);
                }

                foreach ($listingSections as $section => $listingSection) {
                    foreach ($listingSection as $i => $listingWidget) {
                        $content = null;
                        if (is_array($listingWidget)) {
                            $listingWidgetArray = current($listingWidget);
                            if(!empty($listingWidgetArray['content'])) {
                                $content = json_encode($listingWidgetArray['content']);
                            }

                            $listingWidget = key($listingWidget);

                            if(!empty($listingWidgetArray['fields'])) {
                                $fields = $listingWidgetArray['fields'];
                            }
                        }

                        /** @var ListingWidget $listingWidgetReference */
                        if($this->hasReference('LISTING_'. $section . '_' . $listingWidget)) {
                            $listingWidgetReference = $this->getReference('LISTING_' . $section . '_' . $listingWidget);
                        } else {
                            $listingWidgetReference = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findOneBy([
                                'title'   => $listingWidget,
                                'section' => $section
                            ]);
                        }

                        $listingTemplateListingWidget = new ListingTemplateListingWidget();
                        $listingTemplateListingWidget->setListingTemplate($listingTemplateReference);
                        $listingTemplateListingWidget->setListingWidget($listingWidgetReference);
                        $listingTemplateListingWidget->setOrder($i);
                        $listingTemplateListingWidget->setContent($content ?: $this->getReference('LISTING_' . $section . '_' . $listingWidget)->getContent());
                        $listingTemplateListingWidget->setListingTemplateTab($listingTemplateTab);

                        if(!empty($fields)) {
                            foreach($fields as $field) {
                                /** @var ListingTField $templateField */
                                if ($this->hasReference('FIELD_' . $field)) {
                                    $templateField = $this->getReference('FIELD_' . $field);
                                } else {
                                    $templateField = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->findOneBy([
                                        'label'     => $field,
                                        'fieldType' => ListingTField::DEFAULT_TYPE
                                    ]);
                                }

                                $listingTemplateListingWidget->addListingTField($templateField);

                                $templateField->addListingWidget($listingTemplateListingWidget);
                            }
                        }

                        $manager->persist($listingTemplateListingWidget);
                    }
                }
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
        return 5;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
