<?php

namespace ArcaSolutions\ClassifiedBundle\Services;

use ArcaSolutions\ClassifiedBundle\Entity\Classifiedcategory;
use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\WebBundle\Services\BaseCategoryService;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ClassifiedCategoryService
 * @package ArcaSolutions\ClassifiedBundle\Services\Synchronization
 */
class ClassifiedCategoryService extends BaseCategoryService
{
    /**
     * @var ObjectRepository
     */
    protected $repository;
    const SYNCHRONIZATION_SERVICE_NAME = 'classified.category.synchronization';
    /**
     * ClassifiedCategoryService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->repository = $container->get('doctrine')->getRepository('ClassifiedBundle:Classifiedcategory');

        parent::__construct($this->repository, 'classified', $container);
    }

    /**
     * @param array $formData
     * @param BaseCategory|null $category
     * @throws Exception
     */
    public function saveCategory(array $formData, BaseCategory $category = null)
    {
        if(empty($formData['category_id'])) {
            $category = new Classifiedcategory();
        } else {
            $category = $this->repository->find($formData['category_id']);
        }

        parent::saveCategory($formData, $category);
    }
}
