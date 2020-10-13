<?php

namespace ArcaSolutions\ImportBundle\Logic;


use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingTemplateLogic
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Logic
 * @since 11.3.00
 */
class ListingTemplateLogic
{
    /**
     * @var EntityManager
     */
    private $domainManager;
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;
    /**
     * ListingTemplateLogic constructor.
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param EntityManager $domainManager
     */
    public function __construct(EntityManager $domainManager, $container)
    {
        $this->domainManager = $domainManager;
        $this->container = $container;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     * @param string $id The id listing template
     * @param string $name The listing template name
     * @return ListingTemplate
     */
    public function getListingTemplate($id,$name, $listing)
    {
        return $this->findOrCreateListingTemplate($id,$name, $listing);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     * @param integer|null $id The listing
     * @param string|null $name The listing type name
     * @return ListingTemplate
     */
    public function findOrCreateListingTemplate($id,$name, $listing)
    {

        $listingTemplate = $listing->getListingTemplate();
        if($listing->getId() !== null && $listingTemplate !==null){
            return $listingTemplate;
        }
        if (empty($name) && $id !==null ) {
            return $this->findListingTemplateDefault($id);
        }

        if (!$listingTemplate = $this->findListingTemplateByName($name)) {
            $listingTemplate = $this->createListingTemplate($name);
        }

        return $listingTemplate;
    }

    /**
     * @param $id
     * @return object|null
     */
    private function findListingTemplateDefault($id)
    {
        $listingTemplate = $this->domainManager->getRepository(ListingTemplate::class)->find($id);

        return $listingTemplate;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $name
     * @return ListingTemplate
     */
    protected function findListingTemplateByName($name)
    {
        /* @var ListingTemplate $listingTemplate */
        $listingTemplate = $this->domainManager->getRepository(ListingTemplate::class)->findOneByTitle($name);

        return $listingTemplate;
    }

    /**
     * @param $name
     * @return ListingTemplate
     * @throws \Doctrine\ORM\ORMException
     * @since 11.3.00
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     */
    protected function createListingTemplate($name)
    {
        $listingTemplateListingWidgetService = $this->container->get('listingtemplate.listingwidget.service');
        $listingTemplate = $this->container->get('listingtemplate.service')->createNewListingTemplate($name);
        $customDefaultListingWidgets = $listingTemplateListingWidgetService->getCustomDefaultListingWidgets();
        $listingTemplateListingWidgetService->buildTemplate($listingTemplate,$customDefaultListingWidgets);
        return $listingTemplate;
    }
}
