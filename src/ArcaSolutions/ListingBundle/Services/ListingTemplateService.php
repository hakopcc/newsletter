<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use http\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ListingTemplateService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ListingTemplate
     */
    public function createNewListingTemplate($name = '')
    {
        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);

        $em = $this->container->get('doctrine')->getManager();
        $listingTemplate = new ListingTemplate();
        $listingTemplate->setUpdated(new \DateTime());
        $listingTemplate->setEntered(new \DateTime());
        $listingTemplate->setTitle((empty($name)?$this->container->get('translator')->trans('New Listing Template', [], 'widgets', /** @Ignore */
            $sitemgrLanguage):$name));
        $listingTemplate->setSummaryTemplate(1);

        $em->persist($listingTemplate);
        $em->flush();

        $this->container->get('listingtemplatefield.service')->createDefaultListingTemplateFields($listingTemplate);

        return $listingTemplate;
    }

    /**
     * @return ListingTemplate[]
     */
    public function getAllListingTemplates()
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->findAll();
    }
    /**
     * @return ListingTemplate[]
     */
    public function getAllActivesTemplates(){
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->findBy(['status'=>'enabled']);
    }

    /**
     * @param $listingTemplateId
     * @return array
     */
    public function getAllListingWidgetsByListingTemplate($listingTemplateId)
    {
        $listingWidgets = [];
        $listingTabs = [];

        $listingTemplateListingWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingTemplateListingWidget')->findByListingTemplateIdOrderedByOrder($listingTemplateId);

        $listingTemplateTabs = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->findBy(
            ['listingTemplateId' => $listingTemplateId],
            ['order'             => 'ASC']
        );

        foreach ($listingTemplateTabs as $listingTemplateTab) {
            $listingTemplateTabId = $listingTemplateTab->getId();
            $listingWidgets['tabs'][$listingTemplateTabId]['main'] = [];
            $listingWidgets['tabs'][$listingTemplateTabId]['sidebar'] = [];
            $listingTabs[$listingTemplateTabId] = $listingTemplateTab;
        }

        foreach($listingTemplateListingWidgets as $listingTemplateListingWidget) {
            if(!empty($listingTemplateListingWidget->getListingTemplateTab())) {
                $listingTemplateTabId = $listingTemplateListingWidget->getListingTemplateTab()->getId();
                $listingTabs[$listingTemplateTabId] = $listingTemplateListingWidget->getListingTemplateTab();
                $listingWidgetSection = $listingTemplateListingWidget->getListingWidget()->getSection();
                $listingWidgets['tabs'][$listingTemplateTabId][$listingWidgetSection][] = $listingTemplateListingWidget;
            } else {
                $listingWidgetSection = $listingTemplateListingWidget->getListingWidget()->getSection();
                $listingWidgets[$listingWidgetSection][] = $listingTemplateListingWidget;
            }
        }

        return [
            'listingWidgets' => $listingWidgets,
            'listingTabs'    => $listingTabs
        ];
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function deleteListingTemplate($id)
    {
        $deletedSuccessFully = false;
        if (!empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $doctrine = $this->container->get('doctrine');
                $em = $doctrine->getManager();
                $listingTemplate = $doctrine->getRepository('ListingBundle:ListingTemplate')->find($id);

                $em->remove($listingTemplate);
                $em->flush();
                $deletedSuccessFully = true;
            } catch (\Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on deleteListingTemplate method of ListingTemplateService.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    throw $notLoggedCriticalException;
                }
            }
            return $deletedSuccessFully;
        }
    }

    /**
     * @param $id
     */
    public function disableListingTemplate($id)
    {
        try {
            $doctrine = $this->container->get('doctrine');
            $em = $doctrine->getManager();
            $listingTemplate = $doctrine->getRepository('ListingBundle:ListingTemplate')->find($id);
            $listingTemplate->setStatus('disabled');
            $em->persist($listingTemplate);
            $em->flush();
        } catch (Exception $e) {
            $this->container->get('logger')->addError($e->getMessage());
        }
    }
}
