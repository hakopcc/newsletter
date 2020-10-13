<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\WebBundle\Services\BaseCategoryService;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingCategoryService
 * @package ArcaSolutions\ListingBundle\Services\Synchronization
 */
class ListingCategoryService extends BaseCategoryService
{
    /**
     * @var ObjectRepository
     */
    protected $repository;
    const SYNCHRONIZATION_SERVICE_NAME = 'listing.category.synchronization';
    /**
     * ListingCategoryService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->repository = $container->get('doctrine')->getRepository('ListingBundle:ListingCategory');

        parent::__construct($this->repository, 'listing', $container);
    }

    /**
     * @param array $formData
     * @param BaseCategory|null $category
     * @throws Exception
     */
    public function saveCategory(array $formData, BaseCategory $category = null)
    {
        if(empty($formData['category_id'])) {
            $category = new ListingCategory();
        } else {
            $category = $this->repository->find($formData['category_id']);
        }

        parent::saveCategory($formData, $category);
    }

    public function retrieveSerializedCategory($id): string
    {
        $serializedCategory = parent::retrieveSerializedCategory($id);
        /* ModStores Hooks */
        HookFire('listingcategoryservice_before_return_retrieveserializedcategory', [
            'serialized_category' => &$serializedCategory,
            'category_id' => $id
        ]);
        return $serializedCategory;
    }
}
