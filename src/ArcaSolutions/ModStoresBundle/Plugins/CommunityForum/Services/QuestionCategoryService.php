<?php
namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services;

use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\CoreBundle\Logic\FriendlyUrlLogic;
use ArcaSolutions\ImageBundle\Entity\Image;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Question;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\QuestionCategory;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Repository\QuestionRepository;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services\Synchronization\QuestionSynchronizable;
use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\WebBundle\Repository\BaseCategoryRepository;
use ArcaSolutions\WebBundle\Services\BaseCategoryService;
use Closure;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class QuestionCategoryService
 */
class QuestionCategoryService extends BaseCategoryService
{
    /**
     * @var ObjectRepository
     */
    protected $repository;

    const SYNCHRONIZATION_SERVICE_NAME = 'question.category.synchronization';
    const SYNCHRONIZATION_RELATED_OBJECTS_SERVICE_NAME = 'question.synchronization';
    const BANNER_SECTION_IDENTIFIER = 'question';

    /** @var Closure $baseCategoryArrayToIdArrayFunction*/
    private $baseCategoryArrayToIdArrayFunction;

    /**
     * QuestionCategoryService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->repository = $container->get('doctrine')->getRepository('CommunityForumBundle:QuestionCategory');
        $this->baseCategoryArrayToIdArrayFunction = static function($carry, $item) {
            if (is_subclass_of($item,BaseCategory::class)) {
                $itemId = $item->getId();
                if (!empty($itemId)) {
                    if (empty($carry)) {
                        $carry = array();
                    }
                    $carry[] = $itemId;
                }
                unset($itemId);
            }
            return $carry;
        };
        parent::__construct($this->repository, 'forum', $container);
    }


    /**
     * @param int $categoryId
     * @return bool
     * @throws Exception
     */
    public function isAnyEntityAssociatedWithCategoryTreeById($categoryId){
        /** @var BaseCategory $category */
        $category = $this->repository->find($categoryId);
        if ($category === null) {
            unset($category);
            throw new \RuntimeException('Category not found');
        }
        return $this->isAnyEntityAssociatedWithCategoryTree($category);
    }

    /**
     * @param BaseCategory $category
     * @return bool
     * @throws Exception
     */
    public function isAnyEntityAssociatedWithCategoryTree(BaseCategory $category){
        $returnValue = false;
        if($category!==null){
            $categoryId = $category->getId();
            if(!empty($categoryId)) {
                $categoryIdArray = array($categoryId);
                /** @var BaseCategory[] $childCategoryArray */
                $childCategoryIdArray = $this->getChildCategoryIdArray($category);
                $categoryIdArray =  (!empty($childCategoryIdArray))?array_merge($categoryIdArray, $childCategoryIdArray):$categoryIdArray;
                unset($childCategoryArray);
                $doctrine = $this->container->get('doctrine');
                if(!empty($doctrine)&&!empty($categoryIdArray)) {
                    /* @var EntityRepository $bannerRepository */
                    $bannerRepository = $doctrine->getRepository('CommunityForumBundle:Question');
                    if ($bannerRepository !== null) {
                        $qb = $bannerRepository->createQueryBuilder('e');
                        if ($qb !== null) {
                            $whereExpression = $qb->expr()->in('e.category', ':categoryIdArray');
                            $countEntitiesWithinCategoryTreeResult = 0;
                            try {
                                $countEntitiesWithinCategoryTreeResult = $qb->select(
                                    $qb->expr()->count('e.id')
                                )->where($whereExpression)
                                    ->setParameter('categoryIdArray', $categoryIdArray)
                                    ->getQuery()
                                    ->getSingleScalarResult();
                            } catch (NoResultException $e) {
                                $countEntitiesWithinCategoryTreeResult = 0;
                            } catch (Exception $e) {
                                throw $e;
                            }
                            $returnValue = !empty($countEntitiesWithinCategoryTreeResult);
                        }
                    }
                }
                unset($doctrine, $categoryIdArray);
            }
            unset($moduleEntityId);
        }
        return $returnValue;
    }

    /**
     * @param array $formData
     * @param BaseCategory|null $category
     * @throws Exception
     */
    public function saveCategory(array $formData, BaseCategory $category = null)
    {
        $questionCategoryOriginalImage = null;
        $questionCategoryOriginalIcon = null;

        if(empty($formData['category_id'])) {
            $category = new QuestionCategory();
        } else {
            /** @var BaseCategory $category */
            $category = $this->repository->find($formData['category_id']);

            if ($category === null) {
                throw new \RuntimeException('Invalid category');
            }
            $questionCategoryOriginalImage = $category->getImage();
            $questionCategoryOriginalIcon = $category->getIcon();
        }
        $updateFullText = false;
        $em = $this->container->get('doctrine')->getManager();
        if($em!==null) {
            if(!empty($formData['title'])) {
                if ($category->getTitle() !== $formData['title']) {
                    $category->setTitle($formData['title']);
                    $updateFullText = true;
                }
            }
            if (!empty($formData['parent_id'])) {
                /** @var BaseCategory $parentCategory */
                $parentCategory = $this->repository->find($formData['parent_id']);
                $category->setParent($parentCategory);
            }
            $category->setContent($formData['content']);
            if (!empty($formData['friendly_url'])) {
                $friendly_url = $formData['friendly_url'];
            } else {
                $friendly_url = Inflector::friendly_title($formData['title']);
            }

            $friendlyUrlLogic = new FriendlyUrlLogic($this->container);
            $uniqueFriendlyUrl = $friendlyUrlLogic->buildUniqueFriendlyUrl($friendly_url);
            $category->setFriendlyUrl($uniqueFriendlyUrl);

            if (!empty($formData['icon_id'])) {
                /** @var Image $icon */
                $icon = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($formData['icon_id']);
                $category->setIcon($icon);
                $category->setIconId($category->getIcon()->getId());
            } else {
                $category->setIcon(null);
            }

            if (!empty($formData['image_id'])) {
                /** @var Image $image */
                $image = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($formData['image_id']);
                $category->setImage($image);
                $category->setImageId($category->getImage()->getId());
            } else {
                $category->setImage(null);
            }

            if($questionCategoryOriginalIcon !== null && $category->getIconId()!==$questionCategoryOriginalIcon->getId()){
                $em->remove($questionCategoryOriginalIcon);
            }

            if($questionCategoryOriginalImage !== null && $category->getImageId()!==$questionCategoryOriginalImage->getId()){
                $em->remove($questionCategoryOriginalImage);
            }

            $category->setPageTitle($formData['page_title']);

            if($category->getKeywords()!==(empty($formData['keywords'])?null:$formData['keywords'])) {
                if (!empty($formData['keywords'])) {
                    $category->setKeywords($formData['keywords']);
                } else {
                    $category->setKeywords(null);
                }
                $updateFullText = true;
            }

            if (!empty($formData['seo_keywords'])) {
                $category->setSeoKeywords($formData['seo_keywords']);
            } else {
                $category->setSeoKeywords(null);
            }

            if (!empty($formData['seo_description'])) {
                $category->setSeoDescription($formData['seo_description']);
            } else {
                $category->setSeoDescription(null);
            }

            if(strtolower($category->getEnabled())!==(empty($formData['clickToDisable'])?'y':'n')) {
                if (empty($formData['clickToDisable'])) {
                    $category->setEnabled('y');
                } else {
                    $category->setEnabled('n');
                }
                $updateFullText = true;
            }

            if (!empty($formData['featured'])) {
                $category->setFeatured('y');
            } else {
                $category->setFeatured('n');
            }

            if ($category instanceof ListingCategory && !empty($formData['listingTemplate'])) {//TODO: Move it to the ListingCategoryService. Totally wrong here (considering this code as BaseCategory code)
                /** @var ListingTemplate $listingTemplate */
                $listingTemplate = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($formData['listingTemplate']);
                $category->addListingTemplate($listingTemplate);
                $listingTemplate->addCategory($category);
                $em->persist($listingTemplate);
            }

            $em->persist($category);
            $em->flush();
            if ($updateFullText) {
                $this->updateCategoryTreeRelatedEntitiesFullText($category);
            }
        }
        unset($em);
        $this->synchronizeOnElastic($category);
    }

    /**
     * @param int $id
     */
    public function deleteCategory(int $id): void
    {
        /** @var BaseCategory $category */
        $category = $this->repository->find($id);
        if ($category === null) {
            unset($category);
            throw new \RuntimeException('Category not found');
        }
        $em = $this->container->get('doctrine')->getManager();
        if ($em !== null) {
            $categoryImage = $category->getImage();
            $categoryIcon = $category->getIcon();
            $this->updateCategoryTreeRelatedEntitiesFullText($category, true);

            if($categoryImage !== null) {
                $em->remove($categoryImage);
            }
            if($categoryIcon !== null) {
                $em->remove($categoryIcon);
            }
            $childCategoyToBeRemovedArray = $this->getChildCategories($category);
            foreach($childCategoyToBeRemovedArray as $childCategoyToBeRemoved){
                $childCategoryImage = $childCategoyToBeRemoved->getImage();
                $childCategoryIcon = $childCategoyToBeRemoved->getIcon();
                if($childCategoryImage !== null) {
                    $em->remove($childCategoryImage);
                }
                if($childCategoryIcon !== null) {
                    $em->remove($childCategoryIcon);
                }
                $em->remove($childCategoyToBeRemoved);
            }

            $this->removeBannerCategoryRelationOfDeletedCategoryTree($category);
            $this->deleteOnElastic($category);
            $this->executeAfterDeleteOnElasticBeforeDeleteCategory($em, $category);

            $em->remove($category);
            $em->flush();
            unset($questionCategoryImage, $questionCategoryIcon);
        }
        unset($em,$category);
    }

    /**
     * @param BaseCategory $moduleEntity
     * @return array
     */
    public function getParentCategories(BaseCategory $moduleEntity) {
        $returnValue = array();
        $parent = $moduleEntity->getParent();
        if ($parent !== null) {
            $parentCategories = $this->getParentCategories($parent);
            if(!empty($parentCategories)){
                $returnValue = array_merge(array($parent), $parentCategories);
            } else {
                $returnValue = array($parent);
            }
        }
        unset($parent);
        return $returnValue;
    }

    /**
     * @param BaseCategoryRepository $baseCategoryRepository
     * @param int $moduleEntityId
     * @param array $sourceChildCategoryArray
     * @param array $sourceChildCategoryIdArray
     */
    private function recursiveFunctionToRetrieveChildCategories($baseCategoryRepository, $moduleEntityId, &$sourceChildCategoryArray = array(), &$sourceChildCategoryIdArray = array()): void
    {
        $qb = $baseCategoryRepository->createQueryBuilder('c');
        if($qb!==null && is_array($sourceChildCategoryArray) && is_array($sourceChildCategoryIdArray)) {
            $childCategoryOfModuleEntityIdArray = array();
            if(empty($sourceChildCategoryIdArray)) {
                $whereExpression = $qb->expr()->eq('c.categoryId', ':parentCategoryId');

                $childCategoryOfModuleEntityIdArray = $qb->select('c')
                    ->where($whereExpression)
                    ->setParameter('parentCategoryId', $moduleEntityId)
                    ->orderBy('c.id', 'ASC')
                    ->getQuery()
                    ->getResult();
            } else {
                $whereExpression = $qb->expr()->andX(
                    $qb->expr()->eq('c.categoryId', ':parentCategoryId'),
                    $qb->expr()->notIn('c.id', ':categoriesAlreadyInSourceArray')
                );

                $childCategoryOfModuleEntityIdArray = $qb->select('c')
                    ->where($whereExpression)
                    ->setParameter('parentCategoryId', $moduleEntityId)
                    ->setParameter('categoriesAlreadyInSourceArray', $sourceChildCategoryIdArray)
                    ->orderBy('c.id', 'ASC')
                    ->getQuery()
                    ->getResult();
            }
            $childCategoryIdArray = array_reduce($childCategoryOfModuleEntityIdArray, $this->baseCategoryArrayToIdArrayFunction);
            $childCategoryIdArray = empty($childCategoryIdArray)?array():$childCategoryIdArray;
            $sourceChildCategoryIdArray = array_merge($sourceChildCategoryIdArray, $childCategoryIdArray);

            /** @var BaseCategory[] $childCategoryOfModuleEntityIdArray */
            foreach ($childCategoryOfModuleEntityIdArray as $childCategoryOfModuleEntity){
                $childCategoryOfModuleEntityId = $childCategoryOfModuleEntity->getId();
                if(!empty($childCategoryOfModuleEntityId)) {
                    $sourceChildCategoryArray[] = $childCategoryOfModuleEntity;
                    $this->recursiveFunctionToRetrieveChildCategories($baseCategoryRepository, $childCategoryOfModuleEntityId, $sourceChildCategoryArray, $sourceChildCategoryIdArray);
                }
                unset($childCategoryOfModuleEntityId);
            }
            unset($whereExpression, $childCategoryOfModuleEntityIdArray, $childCategoryIdArray);
        }
        unset($qb);
    }

    /**
     * @param BaseCategory $moduleEntity
     * @return int[]
     */
    public function getChildCategoryIdArray(BaseCategory $moduleEntity) {
        return $this->getChildCategoryArrayBase($moduleEntity, true);
    }

    /**
     * @param BaseCategory $moduleEntity
     * @param bool $onlyId
     * @return BaseCategory[]|int[]
     */
    private function getChildCategoryArrayBase(BaseCategory $moduleEntity, $onlyId = false) {
        $returnArray = array();
        $doctrine = $this->container->get('doctrine');
        $moduleEntityId = $moduleEntity->getId();
        if(!empty($doctrine) && !empty($moduleEntityId)) {
            /* @var BaseCategoryRepository $baseCategoryRepository */
            $categoryEntityClass = get_class($moduleEntity);
            $baseCategoryRepository = $doctrine->getRepository($categoryEntityClass);
            if ($baseCategoryRepository !== null) {
                if($onlyId) {
                    $childCategoryArray = array();
                    $this->recursiveFunctionToRetrieveChildCategories($baseCategoryRepository, $moduleEntityId, $childCategoryArray, $returnArray);
                } else {
                    $childCategoryIdArray = array();
                    $this->recursiveFunctionToRetrieveChildCategories($baseCategoryRepository, $moduleEntityId, $returnArray, $childCategoryIdArray);
                }
            }
            unset($baseCategoryRepository);
        }
        unset($doctrine, $moduleEntityId);
        return $returnArray;
    }

    /**
     * @param BaseCategory $moduleEntity
     * @return BaseCategory[]
     */
    public function getChildCategories(BaseCategory $moduleEntity){
        return $this->getChildCategoryArrayBase($moduleEntity);
    }

    /**
     * @param $string
     * @return false|string
     */
    private function formatAddApostWords($string) {
        if (!$string)return false;
        $stringARR=explode(" ",$string);
        foreach ($stringARR as $word){

            if (stripos($word,"'s"))
                $newword=str_replace("'s", "", $word);

            if (stripos($word,"s'"))
                $newword=str_replace("s'", "", $word);

            if ($newword)
                $newStringArr[]=$newword;

            unset($newword);
        }
        if (is_array($newStringArr)){
            $newStringArr=array_unique($newStringArr);
            return (implode(' ',$newStringArr));
        } else return false;
    }

    /**
     * @param BaseCategory $moduleEntity
     * @param bool $considerDeleted
     * @return bool
     */
    public function updateCategoryTreeRelatedEntitiesFullText(BaseCategory $moduleEntity, $considerDeleted = false): bool
    {
        $returnValue = false;
        if($moduleEntity!==null){
            $moduleEntityId = $moduleEntity->getId();
            if(!empty($moduleEntityId)) {
                $categoryArray = array();
                /** @var BaseCategory[] $parentCategoryArray */
                $parentCategoryArray = $this->getParentCategories($moduleEntity);
                if(empty($parentCategoryArray)){
                    $categoryArray[] = $moduleEntity;
                } else {
                    $categoryArray = $parentCategoryArray;
                    $categoryArray[] = $moduleEntity;
                }
                /** @var BaseCategory[] $childCategoryArray */
                $childCategoryArray = $this->getChildCategories($moduleEntity);
                $categoryArray =  (!empty($childCategoryArray))?array_merge($categoryArray, $childCategoryArray):$categoryArray;
                unset($parentCategoryArray, $childCategoryArray);
                $returnValue = $this->updateCategoryArrayRelatedEntitiesFullText($categoryArray, $considerDeleted);
            }
            unset($moduleEntityId);
        }
        return $returnValue;
    }

    /**
     * @param BaseCategory[] $moduleEntityArrayRef
     * @param bool $considerDeleted
     * @return bool
     */
    public function updateCategoryArrayRelatedEntitiesFullText(&$moduleEntityArrayRef, $considerDeleted = false): bool
    {
        $returnValue = false;
        if(!empty($moduleEntityArrayRef) && is_array($moduleEntityArrayRef)){
            //foreach cannot be used here due to performance need
            for($i=0,$count=count($moduleEntityArrayRef);$i<$count;$i++) {
                /** @var BaseCategory $moduleEntityRef */
                $moduleEntityRef = &$moduleEntityArrayRef[$i];
                if ($moduleEntityRef !== null && !empty($moduleEntityRef->getId())) {
                    $this->updateCategoryArrayRelatedEntityFullText($moduleEntityRef,$considerDeleted);
                }
            }
            $returnValue = true;
        }
        return $returnValue;
    }

    /**
     * @param BaseCategory $baseCategory
     * @param bool $considerDeleted
     */
    public function updateCategoryArrayRelatedEntityFullText(BaseCategory $baseCategory, $considerDeleted = false): void
    { //This need to be made in each specific category service instead of the usage of workarounds related to dynamic variables
        $doctrine = $this->container->get('doctrine');
        $baseCategoryId = $baseCategory->getId();
        if(!empty($doctrine) && !empty($baseCategoryId)) {
            /* @var QuestionRepository $baseCategoryRepository */
            $questionRepository = $doctrine->getRepository('CommunityForumBundle:Question');
            if ($questionRepository !== null) {
                $qb = $questionRepository->createQueryBuilder('c');
                if($qb!==null) {
                    $whereExpression = $qb->expr()->eq('c.category', ':categoryId');

                    $questionsWithCategory = $qb->select('c')
                        ->where($whereExpression)
                        ->setParameter('categoryId', $baseCategoryId)
                        ->orderBy('c.id', 'ASC')
                        ->getQuery()
                        ->getResult();

                    //foreach cannot be used here due to performance need
                    for($i=0,$icount=count($questionsWithCategory);$i<$icount;$i++) {
                        /** @var Question $questionRef */
                        $questionRef = &$questionsWithCategory[$i];
                        $fulltextSearchKeywordArray = array();
                        if ($questionRef !== null && !empty($questionRef->getId())) {
                            $questionTitle = $questionRef->getTitle();
                            if (!empty($questionTitle)) {
                                $fulltextSearchKeywordArray[] = $questionTitle;
                                $addKeyword = $this->formatAddApostWords($questionTitle);
                                if(!empty($addKeyword)){
                                    $fulltextSearchKeywordArray[] = $addKeyword;
                                }
                                unset($addKeyword);
                            }
                            unset($questionTitle);

                            $questionKeyWords = $questionRef->getKeywords();
                            $questionKeyWords = str_replace(' || ', ' ', $questionKeyWords);
                            if(!empty($questionKeyWords)){
                                $fulltextSearchKeywordArray[] = $questionKeyWords;
                                $addKeyword = $this->formatAddApostWords($questionKeyWords);
                                if(!empty($addKeyword)){
                                    $fulltextSearchKeywordArray[] = $addKeyword;
                                }
                                unset($addKeyword);
                            }
                            unset($questionKeyWords);

                            $questionDescription = str_replace(["\r", "\n", "\t", "\s{2,}"], ' ',
                                html_entity_decode(strip_tags($questionRef->getDescription()),ENT_QUOTES,'UTF-8'));
                            $questionDescription = preg_replace('/[\x00-\x1F\x7F-\xFF]/', ' ', $questionDescription);
                            $questionDescription = preg_replace('!\s+!', ' ', $questionDescription);

                            $questionDescription = string_substr($questionDescription, 0, 100);
                            if(!empty($questionDescription)){
                                $fulltextSearchKeywordArray[] = $questionDescription;
                            }

                            $questionCategory = $questionRef->getCategory();

                            if(!$considerDeleted && $questionCategory !== null){
                                if(strtolower($questionCategory->getEnabled()) === 'y'){
                                    $this->populateKeyWordArrayWithCategoryData($questionCategory, $fulltextSearchKeywordArray);
                                }
                            }

                            if(!empty($fulltextSearchKeywordArray)){
                                $questionRef->setFulltextsearchKeyword(implode(' ', $fulltextSearchKeywordArray));
                            }

                            /** @var QuestionSynchronizable $synchronizationService */
                            $synchronizationService = $this->container->get($this::SYNCHRONIZATION_RELATED_OBJECTS_SERVICE_NAME);
                            if($synchronizationService !== null) {
                                $synchronizationService->addUpsert($questionRef->getId());
                            }
                            unset($synchronizationService);
                        }
                    }
                }
                unset($qb);
            }
            unset($questionRepository);
        }
        unset($doctrine, $baseCategoryId);
    }

    /**
     * @param BaseCategory $moduleEntity
     * @return bool
     */
    public function removeBannerCategoryRelationOfDeletedCategoryTree(BaseCategory $moduleEntity): bool
    {
        $returnValue = false;
        if($moduleEntity!==null){
            $moduleEntityId = $moduleEntity->getId();
            if(!empty($moduleEntityId)) {
                $categoryArray = array($moduleEntity);
                /** @var BaseCategory[] $childCategoryArray */
                $childCategoryArray = $this->getChildCategories($moduleEntity);
                $categoryArray =  (!empty($childCategoryArray))?array_merge($categoryArray, $childCategoryArray):$categoryArray;
                unset($parentCategoryArray, $childCategoryArray);
                $categoryIdArray = array_reduce($categoryArray, $this->baseCategoryArrayToIdArrayFunction);
                $categoryIdArray = empty($categoryIdArray)?array():$categoryIdArray;
                unset($categoryArray);
                $doctrine = $this->container->get('doctrine');
                if(!empty($doctrine)&&!empty($categoryArray)) {
                    /* @var EntityRepository $bannerRepository */
                    $bannerRepository = $doctrine->getRepository('BannersBundle:Banner');
                    if ($bannerRepository !== null) {
                        $qb = $bannerRepository->createQueryBuilder('b');
                        if ($qb !== null) {
                            $whereExpression = $qb->expr()->andX(
                                $qb->expr()->in('b.categoryId', ':categoryIdArray'),
                                $qb->expr()->eq('b.section', ':categorySection')
                            );
                            $updateBannersQueryResult = $qb->update('b')
                                ->set('b.categoryId', null)
                                ->where($whereExpression)
                                ->setParameter('categoryIdArray', $categoryIdArray)
                                ->setParameter('categorySection', $this::BANNER_SECTION_IDENTIFIER)
                                ->getQuery()
                                ->execute();
                            $returnValue = !empty($updateBannersQueryResult);
                        }
                    }
                }
                unset($doctrine, $categoryIdArray);
            }
            unset($moduleEntityId);
        }
        return $returnValue;
    }

    /**
     * @param BaseCategory $baseCategory
     */
    public function synchronizeOnElastic(BaseCategory $baseCategory): void
    {
        $synchronizationService = $this->container->get($this::SYNCHRONIZATION_SERVICE_NAME);
        if($synchronizationService !== null) {
            $categoryHierarchyToUpsertArray = array($baseCategory);
            $childCategoryToUpsertArray = $this->getChildCategories($baseCategory);
            $categoryHierarchyToUpsertArray = array_merge($categoryHierarchyToUpsertArray, $childCategoryToUpsertArray);
            if(!empty($categoryHierarchyToUpsertArray)) {
                $categoriesIdToUpsertArray = array_reduce($categoryHierarchyToUpsertArray, $this->baseCategoryArrayToIdArrayFunction);
                $categoriesIdToUpsertArray = empty($categoriesIdToUpsertArray)?array():$categoriesIdToUpsertArray;
                $synchronizationService->addUpsert($categoriesIdToUpsertArray);
            }
            unset($categoryHierarchyToUpsertArray);
        }
        unset($synchronizationService);
    }


    /**
     * @param BaseCategory $baseCategory
     */
    public function deleteOnElastic(BaseCategory $baseCategory): void
    {
        $synchronizationService = $this->container->get($this::SYNCHRONIZATION_SERVICE_NAME);
        if($synchronizationService !== null && $baseCategory !== null) {
            $baseCategoryId = $baseCategory->getId();
            if(!empty($baseCategoryId)) {
                $categoryHierarchyToDeleteArray = array($baseCategoryId);
                $childCategoryToDeleteArray = $this->getChildCategoryIdArray($baseCategory);
                $categoryHierarchyToDeleteArray = array_merge($categoryHierarchyToDeleteArray, $childCategoryToDeleteArray);
                if (!empty($categoryHierarchyToDeleteArray)) {
                    $synchronizationService->addDelete($categoryHierarchyToDeleteArray);
                }
                unset($categoryHierarchyToUpsertArray);
            }
            unset($baseCategoryId);
        }
        unset($synchronizationService);
    }

    /**
     * @param ObjectManager $em
     * @param BaseCategory $category
     */
    private function executeAfterDeleteOnElasticBeforeDeleteCategory($em, BaseCategory $category): void
    {
    }

    /**
     * @param QuestionCategory $questionCategory
     * @param string[] $fulltextSearchKeywordArray
     */
    public function populateKeyWordArrayWithCategoryData(QuestionCategory $questionCategory, &$fulltextSearchKeywordArray)
    {
        if($questionCategory!==null && isset($fulltextSearchKeywordArray) && is_array($fulltextSearchKeywordArray)){
            $questionCategoryTitle = $questionCategory->getTitle();
            if(!empty($questionCategoryTitle)) {
                $fulltextSearchKeywordArray[] = $questionCategoryTitle;
            }
            unset($questionCategoryTitle);

            $questionCategoryKeyWords = $questionCategory->getKeywords();
            $questionCategoryKeyWords = str_replace(array("\r\n", "\n"), ' ', $questionCategoryKeyWords);
            if(!empty($questionCategoryKeyWords)) {
                $fulltextSearchKeywordArray[] = $questionCategoryKeyWords;
            }
            unset($questionCategoryKeyWords);

            $questionCategoryParentCategories = $this->getParentCategories($questionCategory);
            if(!empty($questionCategoryParentCategories)) {
                //foreach cannot be used here due to performance need
                for ($j = 0, $jcount = count($questionCategoryParentCategories); $j < $jcount; $j++) {
                    /** @var BaseCategory $parentCategoryRef */
                    $parentCategoryRef = &$questionCategoryParentCategories[$j];
                    if(strtolower($parentCategoryRef->getEnabled()) === 'y') {
                        $parentCategoryTitle = $parentCategoryRef->getTitle();
                        if (!empty($parentCategoryTitle)) {
                            $fulltextSearchKeywordArray[] = $parentCategoryTitle;
                        }
                        unset($parentCategoryTitle);

                        $parentCategoryKeyWords = $parentCategoryRef->getKeywords();
                        $parentCategoryKeyWords = str_replace(array("\r\n", "\n"), ' ', $parentCategoryKeyWords);
                        if (!empty($parentCategoryKeyWords)) {
                            $fulltextSearchKeywordArray[] = $parentCategoryKeyWords;
                        }
                        unset($parentCategoryKeyWords);
                    }
                }
            }
        }
    }
}
