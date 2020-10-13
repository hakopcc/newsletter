<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\CoreBundle\Logic\FriendlyUrlLogic;
use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\ImageBundle\Entity\Image;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Exception;
use JMS\Serializer\SerializationContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Error;

/**
 * Class BaseCategoryService
 * @package ArcaSolutions\WebBundle\Services
 */
abstract class BaseCategoryService
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * BaseCategoryService constructor.
     * @param $repository
     * @param string $moduleName
     * @param ContainerInterface $container
     */
    public function __construct($repository, string $moduleName, ContainerInterface $container)
    {
        $this->moduleName      = $moduleName;
        $this->repository      = $repository;
        $this->container       = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllParentCategories(): array
    {
        return $this->repository->findBy([
            'categoryId' => null
        ], ['title' => 'ASC']);
    }
    /**
     * {@inheritdoc}
     */
    public function getAllParentCategoriesEnabled(): array
    {
        return $this->repository->findBy([
            'categoryId' => null,
            'enabled' => 'y'
        ], ['title' => 'ASC']);
    }
    /**
     * @param $listingTemplateId
     * @return ListingCategory[]
     */
    public function getAllParentCategoriesByListingTemplate($listingTemplateId): array
    {
        /** @var ListingTemplate $listingTemplate */
        $listingTemplate = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($listingTemplateId);

        return $listingTemplate->getCategories()->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildCategoriesById($id): array
    {
        /** @var BaseCategory $category */
        $category = $this->repository->find($id);

        return $category->getChildCategories(false);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildEnabledCategoriesById($id): array
    {

        /** @var BaseCategory $category */
        $category = $this->repository->find($id);

        return $category->getChildCategories(true);
    }
    /**
     * @param int $categoryLevel
     * @param BaseCategory[] $categories
     * @param string $return
     * @param bool $selectParent
     * @param bool $onlyParents
     * @param bool $manageCategories
     * @param array $selectedCategories
     * @throws Twig_Error
     */
    public function buildCategoryTree($categoryLevel, array $categories, &$return = '', $selectParent = false, $onlyParents = false, $manageCategories = false, $selectedCategories = []): void
    {
        $lastElement = end($categories);

        foreach ($categories as $category) {
            if($categoryLevel === 0) {
                $return .= '<div class="categories-item">';
            }

            if(!empty($selectedCategories)) {
                $isSelected = in_array($category->getId(), $selectedCategories);
            } else {
                $isSelected = false;
            }

            $return .= $this->container->get('templating')->render('@Web/category-tree.html.twig', [
                'categoryLevel'    => $categoryLevel,
                'category'         => $category,
                'selectParent'     => $selectParent,
                'onlyParents'      => $onlyParents,
                'manageCategories' => $manageCategories,
                'isSelected'       => $isSelected
            ]);

            if (key($categories) === $lastElement) {
                $return .= '</div>';
            }

            if($categoryLevel === 0) {
                $return .= '</div>';
            }
        }

    }

    /**
     * @param string $term
     * @param $template
     * @param bool $selectParent
     * @param null $listingTemplateId
     * @param array $selectedCategories
     * @return void
     */
    public function buildCategoryTreeByTerm($term, &$template, $selectParent = false, $listingTemplateId = null, $selectedCategories = [])
    {
        if(empty($listingTemplateId)) {
            $categories = $this->repository->searchByTitle($term);
        } else {
            $categories = $this->getAllParentCategoriesByListingTemplate($listingTemplateId);

            if(empty($categories)) {
                $categories = $this->repository->searchByTitle($term);
            } else {
                $arrayCategories = [];

                foreach($categories as $category) {
                    $childCategories = $this->repository->getHierarchyCategories($category->getId());

                    if(!empty($childCategories)) {
                        $criteria = Criteria::create();
                        $criteria->where(Criteria::expr()->contains('title', $term));
                        $arrayCollection = new ArrayCollection($childCategories[0]);

                        $arrayCategories = array_merge($arrayCategories, $arrayCollection->matching($criteria)->getValues());
                    }
                }

                $categories = $arrayCategories;
            }
        }

        if(!empty($categories)) {
            /** @var BaseCategory $category */
            foreach ($categories as $category) {
                if ($selectParent || $category->isLastChild()) {
                    $parentArray = [];

                    $categoriesWithSameTitle = $this->repository->findBy([
                        'title' => $category->getTitle(),
                        'enabled' => 'y'
                    ]);

                    $parent = $category->getParent();

                    while ($parent !== null) {
                        $parentArray[] = $parent;

                        $parent = $parent->getParent();
                    }

                    if ($selectParent && count($parentArray) === 4) {
                        continue;
                    }

                    if (count($categoriesWithSameTitle) > 1) {
                        $parentArray = array_reverse($parentArray);
                    }

                    if(!empty($selectedCategories)) {
                        $isSelected = in_array($category->getId(), $selectedCategories);
                    } else {
                        $isSelected = false;
                    }

                    if($category->getEnabled() !== 'y') {
                        continue;
                    }
                    $template .= $this->container->get('templating')->render('@Web/category.html.twig', [
                        'category'     => $category,
                        'parentArray'  => $parentArray,
                        'selectParent' => $selectParent,
                        'isSelected'   => $isSelected
                    ]);
                }
            }
        } else {
            $template .= $this->container->get('templating')->render('@Web/no-category-found.html.twig');
        }
    }

    /**
     * @param BaseCategory $category
     * @param array $formData
     * @throws Exception
     */
    public function saveCategory(array $formData, BaseCategory $category = null)
    {
        if($category === null) {
            throw new \RuntimeException('Invalid category');
        }

        $em = $this->container->get('doctrine')->getManager();

        $category->setTitle($formData['title']);
        if(!empty($formData['parent_id'])) {
            /** @var BaseCategory $parentCategory */
            $parentCategory = $this->repository->find($formData['parent_id']);
            $category->setParent($parentCategory);
        }

        $category->setContent($formData['content']);
        if(!empty($formData['friendly_url'])) {
            $friendly_url = $formData['friendly_url'];
        } else {
            $friendly_url = Inflector::friendly_title($formData['title']);
        }

        $friendlyUrlLogic = new FriendlyUrlLogic($this->container);

        $uniqueFriendlyUrl = $friendlyUrlLogic->buildUniqueFriendlyUrl($friendly_url);

        $category->setFriendlyUrl($uniqueFriendlyUrl);

        if(!empty($formData['icon_id'])) {
            /** @var Image $icon */
            $icon = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($formData['icon_id']);
            $category->setIcon($icon);
            $category->setIconId($category->getIcon()->getId());
        } else {
            $category->setIcon(null);
        }

        if(!empty($formData['image_id'])) {
            /** @var Image $image */
            $image = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($formData['image_id']);
            $category->setImage($image);
            $category->setImageId($category->getImage()->getId());
        } else {
            $category->setImage(null);
        }

        $category->setPageTitle($formData['page_title']);

        if(!empty($formData['keywords'])) {
            $category->setKeywords($formData['keywords']);
        } else {
            $category->setKeywords(null);
        }

        if(!empty($formData['seo_keywords'])) {
            $category->setSeoKeywords($formData['seo_keywords']);
        } else {
            $category->setSeoKeywords(null);
        }

        $category->setSeoDescription($formData['seo_description']);
        $category->setSeoKeywords($formData['seo_keywords']);

        if(empty($formData['clickToDisable'])) {
            $category->setEnabled('y');
        } else {
            $category->setEnabled('n');
        }

        if(!empty($formData['featured'])) {
            $category->setFeatured('y');
        } else {
            $category->setFeatured('n');
        }

        if($category instanceof ListingCategory && !empty($formData['listingTemplate'])) {
            /** @var ListingTemplate $listingTemplate */
            $listingTemplate = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($formData['listingTemplate']);
            $category->addListingTemplate($listingTemplate);
            $listingTemplate->addCategory($category);
            $em->persist($listingTemplate);
        }

        $em->persist($category);
        $em->flush();
        $this->container->get($this::SYNCHRONIZATION_SERVICE_NAME)->addUpsert([$category->getId()]);
    }

    /**
     * @param $id
     * @return string
     */
    public function retrieveSerializedCategory($id): string
    {
        $serializer = $this->container->get('serializer');

        /** @var BaseCategory $category */
        $category = $this->repository->find($id);

        if($category->getImage() !== null || $category->getIcon() !== null) {
            $this->container->get('utility')->setPackages();
        }

        if($category->getImage() !== null) {
            $imagePath = $this->container->get('templating.helper.assets')
                ->getUrl($this->container->get('imagehandler')->getPath($category->getImage()), 'domain_images');

            $category->getImage()->setUrl($imagePath);
        }

        if($category->getIcon() !== null) {
            $iconPath = $this->container->get('templating.helper.assets')
                ->getUrl($this->container->get('imagehandler')->getPath($category->getIcon()), 'domain_images');

            $category->getIcon()->setUrl($iconPath);
        }

        $manageCategoriesContext = SerializationContext::create();

        $manageCategoriesContext->setGroups('ManageCategories');

        return $serializer->serialize($category, 'json', $manageCategoriesContext);
    }

    /**
     * @param int $id
     */
    public function deleteCategory(int $id): void
    {
        $category = $this->repository->find($id);

        if($category === null) {
            throw new \RuntimeException('Category not found');
        }
        $em = $this->container->get('doctrine')->getManager();

        $em->remove($category);
        $em->flush();
    }
}
