<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use http\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingTemplateTabService
 * @package ArcaSolutions\WysiwygBundle\Services
 */
class ListingTemplateTabService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ListingTemplateTabService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return int
     */
    public function getLastTabId()
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->getLastId();
    }

    /**
     * @param $id
     * @return int|null
     */
    public function deleteListingTemplateTab($id)
    {
        $listingTemplateId = null;

        try {
            $doctrine = $this->container->get('doctrine');
            $em = $doctrine->getManager();
            /** @var ListingTemplateTab $listingTemplateTab */
            $listingTemplateTab = $doctrine->getRepository('ListingBundle:ListingTemplateTab')->find($id);

            if($listingTemplateTab !== null) {
                $listingWidgets = $listingTemplateTab->getListingWidgets()->getValues();

                if(!empty($listingWidgets)) {
                    foreach($listingWidgets as $listingWidget) {
                        $listingTFields = $listingWidget->getListingTFields()->getValues();

                        if (!empty($listingTFields)) {
                            /** @var ListingTField $field */
                            foreach ($listingTFields as $field) {
                                if ($field->getFieldType() !== 'default') {
                                    $em->remove($field);
                                }
                            }
                        }
                    }
                }


                $listingTemplateId = $listingTemplateTab->getListingTemplateId();

                $em->remove($listingTemplateTab);
                $em->flush();
            }
        } catch (Exception $e) {
            $this->container->get('logger')->addError($e->getMessage());
        }

        return $listingTemplateId;
    }
}
