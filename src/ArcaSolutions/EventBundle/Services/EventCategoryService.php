<?php

namespace ArcaSolutions\EventBundle\Services;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\WebBundle\Services\BaseCategoryService;
use ArcaSolutions\EventBundle\Entity\Eventcategory;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EventCategoryService
 * @package ArcaSolutions\EventBundle\Services\Synchronization
 */
class EventCategoryService extends BaseCategoryService
{
    /**
     * @var ObjectRepository
     */
    protected $repository;
    const SYNCHRONIZATION_SERVICE_NAME = 'event.category.synchronization';
    /**
     * EventCategoryService constructor.
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->repository = $container->get('doctrine')->getRepository('EventBundle:Eventcategory');

        parent::__construct($this->repository, 'event', $container);
    }

    /**
     * @param array $formData
     * @param BaseCategory|null $category
     * @throws Exception
     */
    public function saveCategory(array $formData, BaseCategory $category = null)
    {
        if(empty($formData['category_id'])) {
            $category = new Eventcategory();
        } else {
            $category = $this->repository->find($formData['category_id']);
        }

        parent::saveCategory($formData, $category);
    }
}
