<?php

namespace ArcaSolutions\BlogBundle\Services;

use ArcaSolutions\BlogBundle\Entity\Blogcategory;
use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\WebBundle\Services\BaseCategoryService;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BlogCategoryService
 * @package ArcaSolutions\BlogBundle\Services\Synchronization
 */
class BlogCategoryService extends BaseCategoryService
{
    /**
     * @var ObjectRepository
     */
    protected $repository;
    const SYNCHRONIZATION_SERVICE_NAME = 'blog.category.synchronization';
    /**
     * BlogCategoryService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->repository = $container->get('doctrine')->getRepository('BlogBundle:Blogcategory');

        parent::__construct($this->repository, 'blog', $container);
    }

    /**
     * @param array $formData
     * @param BaseCategory|null $category
     * @throws Exception
     */
    public function saveCategory(array $formData, BaseCategory $category = null)
    {
        if(empty($formData['category_id'])) {
            $category = new BlogCategory();
        } else {
            $category = $this->repository->find($formData['category_id']);
        }

        parent::saveCategory($formData, $category);
    }
}
