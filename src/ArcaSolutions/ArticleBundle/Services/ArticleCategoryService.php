<?php

namespace ArcaSolutions\ArticleBundle\Services;

use ArcaSolutions\ArticleBundle\Entity\Articlecategory;
use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\WebBundle\Services\BaseCategoryService;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ArticleCategoryService
 * @package ArcaSolutions\ArticleBundle\Services\Synchronization
 */
class ArticleCategoryService extends BaseCategoryService
{
    /**
     * @var ObjectRepository
     */
    protected $repository;
    const SYNCHRONIZATION_SERVICE_NAME = 'article.category.synchronization';
    /**
     * ArticleCategoryService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->repository = $container->get('doctrine')->getRepository('ArticleBundle:Articlecategory');

        parent::__construct($this->repository, 'article', $container);
    }

    /**
     * @param array $formData
     * @param BaseCategory|null $category
     * @throws Exception
     */
    public function saveCategory(array $formData, BaseCategory $category = null)
    {
        if(empty($formData['category_id'])) {
            $category = new Articlecategory();
        } else {
            $category = $this->repository->find($formData['category_id']);
        }

        parent::saveCategory($formData, $category);
    }
}
