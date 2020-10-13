<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Services;


use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\CoreBundle\Services\Modules;
use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Entity\SettingNavigationDropdownMenu;
use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Repository\DropdownMenuRepository;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\MultiDomainBundle\Services\Settings;
use ArcaSolutions\CoreBundle\Services\Settings as MainSettings;
use ArcaSolutions\WebBundle\Entity\SettingNavigation;
use ArcaSolutions\WebBundle\Repository\SettingNavigationRepository;
use ArcaSolutions\WebBundle\Services\NavigationService;
use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Repository\PageRepository;
use ArcaSolutions\WysiwygBundle\Services\PageService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Base;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\TranslatorInterface;

class NavigationDropdownMenuService
{
    /** @var NavigationService $navigationService */
    private $navigationService;

    /** @var Settings $multidomainSettings */
    private $multidomainSettings;

    /** @var MainSettings $mainSettings */
    private $mainSettings;

    /** @var DoctrineRegistry $doctrine */
    private $doctrine;

    /** @var ContainerInterface $container */
    private $container;

    /** @var Modules $modules */
    private $modules;

    /** @var LanguageHandler $languageHandler */
    private $languageHandler;

    /** @var TranslatorInterface $translatorService */
    private $translatorService;

    private $sitemanagerLang=null;

    private $domainLang=null;

    /**
     * NavigationService constructor.
     * @param Settings $multidomainSettings
     * @param MainSettings $mainSettings
     * @param DoctrineRegistry $doctrine
     * @param ContainerInterface $container
     * @param Modules $modules
     * @param NavigationService $navigationService
     * @param LanguageHandler $languageHandler
     * @param TranslatorInterface $translatorService
     */
    public function __construct(Settings $multidomainSettings, MainSettings $mainSettings, DoctrineRegistry $doctrine, ContainerInterface $container, Modules $modules, NavigationService $navigationService, LanguageHandler $languageHandler, TranslatorInterface $translatorService)
    {
        $this->mainSettings = $mainSettings;
        $this->multidomainSettings = $multidomainSettings;
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->modules = $modules;
        $this->navigationService = $navigationService;
        $this->languageHandler = $languageHandler;
        $this->translatorService = $translatorService;
    }

    /**
     * @return array
     */
    public function getArrayModules()
    {
        $headerNavigationPages = $this->navigationService->getNavigationPages(ucfirst('header'));

        $arrayModulesReturn = $headerNavigationPages['mainPages'];
        $this->navigationService->removesDisabledModules(ucfirst('header'), $arrayModulesReturn);

        $arrayModulesReturn[] = array(
            'name' => LANG_SITEMGR_NAVIGATION_CUSTOM_LINK,
            'url' => 'custom',
            'page_id' => null
        );
        $arrayModulesReturn[] = array(
            'name' => $this->getTranslatedString('Menu with sub menus', true, 'system'),
            'url' => 'dropdown',
            'page_id' => null
        );

        if (!empty($headerNavigationPages['customPages'])) {
            $arrayModulesReturn = array_merge($arrayModulesReturn, $headerNavigationPages['customPages']);
        }
        return $arrayModulesReturn;
    }

    /**
     * @param $page Page
     *
     * @return string
     * @throws Exception
     */
    protected function getFinalPageUrl($page)
    {
        $pageTypeTitle = $page->getPageType()->getTitle();

        $uri = $this->container->get('pagetype.service')->getModuleUri($pageTypeTitle);

        $scheme = null;

        /** @var Settings $multiDomainInformation */
        $multiDomainInformation = $this->container->get('multi_domain.information');

        $domainUrl = str_replace('_','-',$multiDomainInformation->getActiveHost());

        try {
            $currentRequest = $this->container->get('request_stack')->getCurrentRequest();
            if($currentRequest!==null){
                $scheme = $currentRequest->getScheme().'://';
            } else {
                $sslEnabledGlobalConstant = null;
                try {
                    $sslEnabledGlobalConstant = @constant('SSL_ENABLED');
                } catch (Exception $constException) {
                    //Do nothing. Just consider constant nonexistent
                }
                if(empty($sslEnabledGlobalConstant)) {
                    //If $sslEnabledGlobalConstant, identify which domain is and what custom folder it uses (Eg.: web/custom/domain_1); Then open ssl.inc.php (Eg.: web/custom/domain_1/conf/ssl.inc.php) and identify if ssl is enabled by the SSL_ENABLED define (eg.: define("SSL_ENABLED",       "on")) and set the value of $sslEnabledGlobalConstant.
                    $kernelRootDir = $this->container->get('kernel')->getRootDir();
                    $domainId = $multiDomainInformation->getId();
                    $sslIncFilePath = $kernelRootDir . '/../web/custom/domain_' . $domainId . '/conf/ssl.inc.php';
                    if (file_exists($sslIncFilePath)) {
                        $sslIncHandle = fopen($sslIncFilePath, 'r');
                        if (!empty($sslIncHandle)) {
                            $sslIncFileSize = filesize($sslIncFilePath);
                            $sslIncContents = fread($sslIncHandle, $sslIncFileSize);
                            if (!empty($sslIncContents)) {
                                $pattern = '/^.*define\s?\([\'"]SSL_ENABLED[\'"]\s?,[\t\s\'"]*([^\t\s\'"]*).*$/m';
                                $match = null;
                                $success = preg_match($pattern, $sslIncContents, $match);
                                if($success && !empty($match) && is_array($match) && !empty($match[1])){
                                    $sslEnabledGlobalConstant = $match[1];
                                }
                            }
                            fclose($sslIncHandle);
                        }
                    }
                }
                if(empty($sslEnabledGlobalConstant)){
                    $sslEnabledGlobalConstant = 'on';
                }
                $scheme = ($sslEnabledGlobalConstant === 'on' ? 'https://' : 'http://');
            }
        } catch (Exception $e) {
            throw $e;
        }

        $pageUrl = $scheme.$domainUrl.((!empty($uri)) ? '/'. $uri : '').'/'.$page->getUrl();

        $pageUrl .= $pageTypeTitle === PageType::CUSTOM_PAGE ? '.html' : '';

        return $pageUrl;
    }


    /**
     * @param SettingNavigation $settingNavigationItem
     * @param PageService $pageService
     * @return mixed
     * @throws Exception
     */
    private function buildMenuOption($settingNavigationItem, $pageService){
        if(empty($settingNavigationItem)){
            return null;
        }

        $page = $settingNavigationItem->getPage();
        $pageId = (!empty($page))?$page->getId():null;

        $pageUrl = (!empty($page))?$this->getFinalPageUrl($page):null;

        $returnValue = array(
            'order' => $settingNavigationItem->getOrder(),
            'area' => $settingNavigationItem->getArea(),
            'pageId' => $pageId,
            'label' => $settingNavigationItem->getLabel(),
            'custom' => $settingNavigationItem->getCustom(),
            'link' => $settingNavigationItem->getLink(),
            'pageUrl' => $pageUrl,
            'children' => array()
        );
        unset($page);
        return $returnValue;
    }

    /**
     * @param $pageId
     * @return string|null
     */
    public function tryGetPageTitle($pageId)
    {
        $returnValue = null;
        if (!empty($pageId)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            try {
                if ($this->doctrine !== null) {
                    /** @var PageRepository $pageRepository */
                    $pageRepository = $this->doctrine->getRepository('WysiwygBundle:Page');
                    if ($pageRepository !== null) {
                        $qb = $pageRepository->createQueryBuilder('page');
                        if ($qb !== null) {
                            $queryResultValue = null;
                            try {
                                $queryResultValue = $qb->select('page.title')
                                    ->where($qb->expr()->eq('page.id', ':pageId'))
                                    ->setParameter('pageId', $pageId)
                                    ->getQuery()
                                    ->getSingleScalarResult();
                            } catch (NoResultException $e) {
                                //DO NOTHING
                            } catch (NonUniqueResultException $e) {
                                //DO NOTHING
                            } catch (Exception $e) {
                                throw $e;
                            }
                            $returnValue = (!empty($queryResultValue) && is_string($queryResultValue)) ? (string)$queryResultValue : null;
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on tryGetPageName method of NavigationDropdownMenuService.php', ['exception' => $e]);
                }
            } finally {
                unset($logger);
            }
        }
        return $returnValue;
    }

    /**
     * @param bool $includeEmptyDropdowns
     * @param bool $includeDeactivatedModulesPages
     * @param bool $includeEmptyCustomLinks
     * @return array
     * @throws Exception
     */
    public function getHeader($includeEmptyDropdowns = false, $includeDeactivatedModulesPages = false, $includeEmptyCustomLinks = false){
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        $headerItemsReturnArray = array();
        try {
            if($this->doctrine!==null && $this->modules!==null && $this->navigationService!==null) {
                /** @var PageService $pageService */
                $pageService = $this->container->get('page.service');
                /** @var SettingNavigationRepository $settingNavigationRepository */
                $settingNavigationRepository = $this->doctrine->getRepository('WebBundle:SettingNavigation');
                /** @var DropdownMenuRepository $settingNavigationDropdownMenu */
                $settingNavigationDropdownMenu = $this->doctrine->getRepository('DropdownMenuBundle:SettingNavigationDropdownMenu');
                if ($pageService!==null && $settingNavigationRepository !== null && $settingNavigationDropdownMenu !== null) {
                    $qb = $settingNavigationRepository->createQueryBuilder('sn');
                    if($qb!==null) {
                        $whereExpression = (!$includeEmptyCustomLinks)?$qb->expr()->andX(
                            $qb->expr()->in('sn.area', ':areas'),
                            $qb->expr()->orX()->addMultiple([
                                $qb->expr()->isNotNull('sn.pageId'),
                                $qb->expr()->andX()->addMultiple([
                                    $qb->expr()->eq('sn.custom',$qb->expr()->literal('1')),
                                    $qb->expr()->isNotNull('sn.link'),
                                    $qb->expr()->neq($qb->expr()->trim('sn.link'),$qb->expr()->literal(''))
                                ]),
                                $qb->expr()->andX(
                                    $qb->expr()->eq('sn.custom',$qb->expr()->literal('0')),
                                    $qb->expr()->orX(
                                        $qb->expr()->eq('sn.area',$qb->expr()->literal('header_dropdown')),
                                        $qb->expr()->andX()->addMultiple([
                                            $qb->expr()->eq('sn.area',$qb->expr()->literal('header')),
                                            $qb->expr()->isNotNull('sn.link'),
                                            $qb->expr()->eq('sn.link',$qb->expr()->literal('dropdown'))
                                        ])
                                    )
                                )
                            ])
                        ):$qb->expr()->andX(
                            $qb->expr()->in('sn.area', ':areas'),
                            $qb->expr()->orX()->addMultiple([
                                $qb->expr()->isNotNull('sn.pageId'),
                                $qb->expr()->eq('sn.custom',$qb->expr()->literal('1')),
                                $qb->expr()->eq('sn.area',$qb->expr()->literal('header_dropdown')),
                                $qb->expr()->andX()->addMultiple([
                                    $qb->expr()->eq('sn.area',$qb->expr()->literal('header')),
                                    $qb->expr()->isNotNull('sn.link'),
                                    $qb->expr()->eq('sn.link',$qb->expr()->literal('dropdown'))
                                ])
                            ])
                        );

                        $menuItensWithDropdown = $qb->select('sn')
                            ->where($whereExpression)
                            ->setParameter('areas', array('header','header_dropdown'))
                            ->orderBy('sn.order', 'ASC')
                            ->getQuery()
                            ->getResult();

                        $cleanedMenuItemsWithDropdownChild = array();
                        $menuItemChildMappings = array();
                        $cleanedMenuItemsWithDropdown = array();
                        /** @var SettingNavigation $menuItemWithDropdown */
                        foreach ($menuItensWithDropdown as $menuItemWithDropdown){
                            $order = $menuItemWithDropdown->getOrder();
                            $area = $menuItemWithDropdown->getArea();
                            $isChild = false;
                            $hasChild = false;
                            $menuOptionToAdd = null;
                            $menuItemChildMappingItem = null;
                            if($order!==null && is_numeric($order) && $order>=0) {
                                /** @var SettingNavigationDropdownMenu[] $childDropdownItems */
                                $childDropdownItems = $settingNavigationDropdownMenu->findBy(array(
                                    'parentMenu' => $order
                                ));
                                if(count($childDropdownItems)>0) {
                                    $hasChild = true;
                                    if($area === 'header') {
                                        $menuItemWithDropdown->setArea('header_dropdown');
                                    }
                                    $menuItemChildMappingItem = array();
                                    foreach ($childDropdownItems as $childDropdownItem){
                                        $menuItemChildMappingItem[] = $childDropdownItem->getId();
                                    }
                                    $menuOptionToAdd = $menuItemWithDropdown;
                                } else {
                                    $settingNavigationDropdownMenuRegCount = $settingNavigationDropdownMenu->count(array(
                                        'id' => $order
                                    ));
                                    if($settingNavigationDropdownMenuRegCount>0) {
                                        $notChildOfCriteria = Criteria::create()->where(
                                            Criteria::expr()->andX(
                                                Criteria::expr()->eq('id', $order),
                                                Criteria::expr()->isNull('parentMenu')
                                            )
                                        );
                                        $settingNavigationDropdownMenuRegWithParent = $settingNavigationDropdownMenu->countByCriteria($notChildOfCriteria);
                                        if($settingNavigationDropdownMenuRegWithParent==0){
                                            $isChild = true;
                                            if($area === 'header') {
                                                $menuItemWithDropdown->setArea('header_dropdown');
                                            }
                                            $menuOptionToAdd = $menuItemWithDropdown;
                                        }
                                        else
                                        {
                                            $menuOptionToAdd = $menuItemWithDropdown;
                                        }
                                    } else {
                                        $menuItemWithDropdownCustom = $menuItemWithDropdown->getCustom();
                                        $menuItemWithDropdownPage = $menuItemWithDropdown->getPage();
                                        $menuItemWithDropdownLink = $menuItemWithDropdown->getLink();
                                        if($area==='header_dropdown'||($area==='header'&&empty($menuItemWithDropdownPage)&&!$menuItemWithDropdownCustom&&$menuItemWithDropdownLink==='dropdown')) {
                                            if(!$includeEmptyDropdowns){
                                                continue;
                                            }
                                        }
                                        $menuOptionToAdd = $menuItemWithDropdown;

                                        unset($menuItemWithDropdownCustom,$menuItemWithDropdownPage,$menuItemWithDropdownLink);
                                    }
                                }

                                $page = $menuItemWithDropdown->getPage();

                                if (empty($page) || $includeDeactivatedModulesPages) {
                                    if($isChild){
                                        $cleanedMenuItemsWithDropdownChild[$menuOptionToAdd->getOrder()]=$this->buildMenuOption($menuOptionToAdd,$pageService);
                                    } else {
                                        $cleanedMenuItemsWithDropdown[$menuOptionToAdd->getOrder()]=$this->buildMenuOption($menuOptionToAdd,$pageService);
                                        if($hasChild && !empty($menuItemChildMappingItem)){
                                            $menuItemChildMappings[$menuOptionToAdd->getOrder()] = $menuItemChildMappingItem;
                                        }
                                    }
                                    continue;
                                }

                                $pageModule = null;
                                if ($page->getPageType()->getTitle() !== 'Custom Page') {
                                    $pageModule = $this->navigationService->mainHeaderNavigation[$page->getPageType()->getTitle()]['module'];
                                }
                                $modulesAvailable = $this->modules->getAvailableModules();
                                if (null === $pageModule || !array_key_exists($pageModule,$modulesAvailable) || $modulesAvailable[$pageModule] || null === $modulesAvailable[$pageModule]) {
                                    if($isChild){
                                        $cleanedMenuItemsWithDropdownChild[$menuOptionToAdd->getOrder()]=$this->buildMenuOption($menuOptionToAdd,$pageService);
                                    } else {
                                        $cleanedMenuItemsWithDropdown[$menuOptionToAdd->getOrder()]=$this->buildMenuOption($menuOptionToAdd,$pageService);
                                        if($hasChild && !empty($menuItemChildMappingItem)){
                                            $menuItemChildMappings[$menuOptionToAdd->getOrder()] = $menuItemChildMappingItem;
                                        }
                                    }
                                }
                            }
                        }

                        $emptyDropdownsKeysArray = array();

                        foreach($menuItemChildMappings as $menuItemKey => $menuItemChildMapping){ // Iterate through menu item child mapping
                            if(array_key_exists($menuItemKey, $cleanedMenuItemsWithDropdown)){ // If menu item exists (are not
                                $emptyDropdown = true;
                                foreach ($menuItemChildMapping as $menuItemChildKey) { // Iterate through dropdown menu child item mapping
                                    if (array_key_exists($menuItemChildKey, $cleanedMenuItemsWithDropdownChild)) { //If child exists on child list
                                        if((!empty($cleanedMenuItemsWithDropdownChild[$menuItemChildKey]['pageId']))||$includeEmptyCustomLinks||($cleanedMenuItemsWithDropdownChild[$menuItemChildKey]['custom']&&!empty($cleanedMenuItemsWithDropdownChild[$menuItemChildKey]['link']))) {
                                            $cleanedMenuItemsWithDropdown[$menuItemKey]['children'][] = $cleanedMenuItemsWithDropdownChild[$menuItemChildKey];
                                            $emptyDropdown = false;
                                        }
                                    }
                                }
                                if($emptyDropdown){
                                    $emptyDropdownsKeysArray[]=$menuItemKey;
                                }
                            }
                        }

                        if(!$includeEmptyDropdowns) { // Remove empty dropdowns
                            foreach ($emptyDropdownsKeysArray as $emptyDropdownsKey) {
                                unset($cleanedMenuItemsWithDropdown[$emptyDropdownsKey]);
                            }
                        }

                        $headerItemsReturnArray = array_values($cleanedMenuItemsWithDropdown);
                    }
                    unset($qb);
                }
                unset($settingNavigationRepository, $settingNavigationDropdownMenu);
            }
        } catch (Exception $e) {
            if ($logger !== null) {
                $logger->critical('Unexpected error on getHeader method of NavigationDropdownMenuService.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
        } finally {
            unset($logger);
            if ($notLoggedCriticalException !== null) {
                throw $notLoggedCriticalException;
            }
        }
        return $headerItemsReturnArray;
    }

    private function validateDdmPluginNewDataEntryStructure($dataEntry){
        return array_key_exists('deleted', $dataEntry) && array_key_exists('area', $dataEntry) && array_key_exists('order', $dataEntry) && array_key_exists('label', $dataEntry) && array_key_exists('custom', $dataEntry);
    }

    private function validateDdmPluginOriginalDataEntryStructure($dataEntry){
        return array_key_exists('order', $dataEntry) && array_key_exists('area', $dataEntry) && array_key_exists('label', $dataEntry) && array_key_exists('custom', $dataEntry);
    }

    private function ddmPluginDataOriginalEntryIsEmpty($dataEntry){
        return $dataEntry['area']===''&&
        $dataEntry['order']===''&&
        $dataEntry['label']===''&&
        $dataEntry['pageId']===''&&
        $dataEntry['custom']===''&&
        $dataEntry['link']===''&&
        $dataEntry['parentId']==='';
    }

    private function ddmPluginDataNewEntryIsEmpty($dataEntry){
        return $dataEntry['deleted']===''&&
            $dataEntry['area']===''&&
            $dataEntry['order']===''&&
            $dataEntry['label']===''&&
            $dataEntry['pageId']===''&&
            $dataEntry['custom']===''&&
            $dataEntry['link']===''&&
            $dataEntry['parentId']==='';
    }

    public function tryToSaveNavigationForm(&$postArrayRef, &$errorMessage)
    {
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        $returnValue = false;
        try {
            if($this->doctrine===null || empty($postArrayRef)) {
                throw new Exception('Can not get Doctrine Registry or HTTP Post array is empty.');
            }
            /** @var SettingNavigationRepository $settingNavigationRepository */
            $settingNavigationRepository = $this->doctrine->getRepository('WebBundle:SettingNavigation');
            /** @var DropdownMenuRepository $settingNavigationDropdownMenu */
            $settingNavigationDropdownMenuRepository = $this->doctrine->getRepository('DropdownMenuBundle:SettingNavigationDropdownMenu');
            /** @var PageRepository $pageRepository */
            $pageRepository = $this->doctrine->getRepository('WysiwygBundle:Page');
            if($settingNavigationRepository===null || $settingNavigationDropdownMenuRepository===null || $pageRepository===null){
                unset($settingNavigationRepository,$settingNavigationDropdownMenuRepository);
                throw new Exception('Can not get Doctrine SettingNavigation repository or SettingNavigationDropdownMenu repository or Page repository.');
            }
            if(!array_key_exists('ddm_plugin_navigation', $postArrayRef)){
                throw new Exception('Dropdown plugin HTTP Post array key was not found.');
            }
            $ddmPluginNavigationFromPost = $postArrayRef['ddm_plugin_navigation'];
            /** @var EntityManagerInterface $em */
            $em = $this->doctrine->getManager();
            if (empty($em)) {
                unset($ddmPluginNavigationFromPost, $em);
                throw new Exception('Can not get Doctrine Entity Manager.');
            }
            try {
                $em->beginTransaction(); // Deal with new structure: 1 - Start a new transaction (suspend auto-commit)
                $deletedEntriesIndexes = array();
                $deletedChildEntriesIndexes = array();
                $indexesToIgnore = array();
                $updatedEntriesIndexes = array();
                $updatedChildEntriesIndexes = array();
                $createdEntriesIndexes = array();
                $createdChildEntriesIndexes = array();
                $needFlush = false;

                for($entryIndex=0, $entriesCount = count($ddmPluginNavigationFromPost); $entryIndex<$entriesCount; $entryIndex++){
                    $currentEntryRef = &$ddmPluginNavigationFromPost[$entryIndex];
                    $hasOriginalValues = array_key_exists('original', $currentEntryRef) && !$this->ddmPluginDataOriginalEntryIsEmpty($currentEntryRef['original']);
                    $hasNewValues = array_key_exists('new', $currentEntryRef) && !$this->ddmPluginDataNewEntryIsEmpty($currentEntryRef['new']);
                    if(!$hasNewValues){
                        unset($deletedEntriesIndexes, $deletedOutsideDatabaseEntriesIndexes, $updatedEntriesIndexes, $createdEntriesIndexes, $entryIndex, $entriesCount, $hasOriginalValues, $hasNewValues);
                        throw new Exception('One of dropdown plugin HTTP Post array entries has invalid structure - there is no data about an navigation menu entry.');
                    }
                    $currentEntryNewRef = &$currentEntryRef['new'];
                    if(!$this->validateDdmPluginNewDataEntryStructure($currentEntryNewRef)){
                        unset($deletedEntriesIndexes, $deletedOutsideDatabaseEntriesIndexes, $updatedEntriesIndexes, $createdEntriesIndexes, $entryIndex, $entriesCount, $hasOriginalValues, $hasNewValues);
                        throw new Exception('One of dropdown plugin HTTP Post array entries has invalid structure - there is a missing field in navigation menu entry data.');
                    }
                    $markedAsDeleted = $currentEntryNewRef['deleted']==='1';
                    if(!$hasOriginalValues){//Sure that is relative to a new entry or a non-saved deleted entry
                        if($markedAsDeleted){//It is a non-saved deleted entry. Will be added to indexes to ignore
                            $indexesToIgnore[] = $entryIndex;
                        } else {//It is a new entry
                            $createdEntriesIndexes[] = $entryIndex;
                            $parentIdExistsOnNew = array_key_exists('parentId', $currentEntryNewRef)&&$currentEntryNewRef['parentId']!==null&&$currentEntryNewRef['parentId']!=='';
                            if($parentIdExistsOnNew){
                                $createdChildEntriesIndexes[] = $entryIndex;
                            }
                        }
                    } else {
                        $currentEntryOriginalRef = &$currentEntryRef['original'];
                        if(!$this->validateDdmPluginOriginalDataEntryStructure($currentEntryOriginalRef)){
                            unset($markedAsDeleted, $deletedEntriesIndexes, $deletedOutsideDatabaseEntriesIndexes, $updatedEntriesIndexes, $createdEntriesIndexes, $entryIndex, $entriesCount, $hasOriginalValues, $hasNewValues);
                            throw new Exception('One of dropdown plugin HTTP Post array entries has invalid structure - there is a missing field in navigation menu original entry data.');
                        }
                        $parentIdExistsOnOriginal = array_key_exists('parentId', $currentEntryOriginalRef)&&$currentEntryOriginalRef['parentId']!==null&&$currentEntryOriginalRef['parentId']!=='';
                        if($markedAsDeleted){
                            $deletedEntriesIndexes[] = $entryIndex;
                            if($parentIdExistsOnOriginal) {
                                $deletedChildEntriesIndexes[] = $entryIndex;
                            }
                        } else {
                            $pageIdExistsOnOriginal = array_key_exists('pageId', $currentEntryOriginalRef)&&$currentEntryOriginalRef['pageId']!==null&&$currentEntryOriginalRef['pageId']!=='';
                            $pageIdExistsOnNew = array_key_exists('pageId', $currentEntryNewRef)&&$currentEntryNewRef['pageId']!==null&&$currentEntryNewRef['pageId']!=='';
                            $hasChangedPageId = !(($pageIdExistsOnOriginal === $pageIdExistsOnNew) && ((!$pageIdExistsOnNew && !$pageIdExistsOnOriginal) || ($currentEntryOriginalRef['pageId'] === $currentEntryNewRef['pageId'])));
                            $isInvalidPageAndWillBeDeleted = false;
                            if($pageIdExistsOnNew){
                                if(is_numeric($currentEntryNewRef['pageId']) && !empty($currentEntryNewRef['pageId'])) {
                                    $page = $pageRepository->find($currentEntryNewRef['pageId']);
                                    if($page===null){
                                        $isInvalidPageAndWillBeDeleted = true;
                                    }
                                    unset($page);
                                } else {
                                    $isInvalidPageAndWillBeDeleted = true;
                                }
                            }
                            if($isInvalidPageAndWillBeDeleted){//When in the meantime the page was deleted or if the received value for page id is not numeric or empty or zero
                                $deletedEntriesIndexes[] = $entryIndex;
                                if($parentIdExistsOnOriginal) {
                                    $deletedChildEntriesIndexes[] = $entryIndex;
                                }
                            } else {
                                $hasChangedOrder = $currentEntryOriginalRef['order'] !== $currentEntryNewRef['order'];

                                $hasChangedArea = $currentEntryOriginalRef['area'] !== $currentEntryNewRef['area'];
                                $hasChangedLabel = $currentEntryOriginalRef['label'] !== $currentEntryNewRef['label'];
                                $hasChangedCustom = $currentEntryOriginalRef['custom'] !== $currentEntryNewRef['custom'];

                                $linkExistsOnOriginal = array_key_exists('link', $currentEntryOriginalRef)&&!empty($currentEntryOriginalRef['link']);
                                $linkExistsOnNew = array_key_exists('link', $currentEntryNewRef)&&!empty($currentEntryNewRef['link']);
                                $hasChangedLink = !(($linkExistsOnOriginal === $linkExistsOnNew) && ((!$linkExistsOnOriginal&&!$linkExistsOnNew) || ($currentEntryOriginalRef['link'] === $currentEntryNewRef['link'])));

                                $parentIdExistsOnNew = array_key_exists('parentId', $currentEntryNewRef)&&$currentEntryNewRef['parentId']!==null&&$currentEntryNewRef['parentId']!=='';
                                $hasChangedParentId = !(($parentIdExistsOnOriginal === $parentIdExistsOnNew) && ((!$parentIdExistsOnOriginal && !$parentIdExistsOnNew) || ($currentEntryOriginalRef['parentId'] === $currentEntryNewRef['parentId'])));
                                if ($hasChangedOrder || $hasChangedArea || $hasChangedLabel || $hasChangedCustom || $hasChangedPageId || $hasChangedLink || $hasChangedParentId) {
                                    if($hasChangedOrder){//If has modifications on order, delete item then recreate then (because order belongs of primary keys of table and could generate unexpected behavior if we just update it inside a transaction)
                                        $deletedEntriesIndexes[] = $entryIndex;
                                        $createdEntriesIndexes[] = $entryIndex;
                                        if($parentIdExistsOnOriginal) {
                                            $deletedChildEntriesIndexes[] = $entryIndex;
                                        }
                                        if($parentIdExistsOnNew) {
                                            $createdChildEntriesIndexes[] = $entryIndex;
                                        }
                                    } else {
                                        $updatedEntriesIndexes[] = $entryIndex;
                                        if ($hasChangedParentId) {
                                            if ($parentIdExistsOnOriginal && $parentIdExistsOnNew) {
                                                $updatedChildEntriesIndexes[] = $entryIndex;
                                            } elseif ($parentIdExistsOnOriginal) {
                                                $deletedChildEntriesIndexes[] = $entryIndex;
                                            } else {
                                                $createdChildEntriesIndexes[] = $entryIndex;
                                            }
                                        }
                                    }
                                } else {
                                    $indexesToIgnore[] = $entryIndex;
                                }
                                unset($hasChangedOrder, $hasChangedArea, $hasChangedLabel, $hasChangedCustom,
                                    $linkExistsOnOriginal, $linkExistsOnNew, $hasChangedLink,
                                    $parentIdExistsOnOriginal, $parentIdExistsOnNew, $hasChangedParentId);
                            }
                            unset($pageIdExistsOnOriginal, $pageIdExistsOnNew,$hasChangedPageId,$isInvalidPageAndWillBeDeleted);
                        }
                    }
                }
                unset($entryIndex, $entriesCount);

                foreach($deletedEntriesIndexes as $deletedEntryIndex){//Deal with new structure: 2 - Exclude all entries marked as deleted on new array (except the entries that has original empty);
                    $willBeDeletedEntryRef = &$ddmPluginNavigationFromPost[$deletedEntryIndex];
                    $willBeDeletedOriginalEntryRef = &$willBeDeletedEntryRef['original'];
                    $willBeDeletedEntryOrder = $willBeDeletedOriginalEntryRef['order'];
                    $willBeDeletedEntryArea = $willBeDeletedOriginalEntryRef['area'];
                    $willBeDeletedSettingNavigation = $settingNavigationRepository->findOneBy(array('order'=>$willBeDeletedEntryOrder, 'area'=>$willBeDeletedEntryArea));
                    if($willBeDeletedSettingNavigation!==null){
                        $em->remove($willBeDeletedSettingNavigation);
                        $needFlush = true;
                    }
                    unset($willBeDeletedEntryOrder, $willBeDeletedEntryOrder, $willBeDeletedEntryArea, $willBeDeletedSettingNavigation);
                }
                unset($deletedEntryIndex, $deletedEntriesIndexes);

                if($needFlush){
                    $em->flush();//Needed to avoid conflicts with non persisted deletions with create SettingNavigation entities block
                    $needFlush = false;
                }

                foreach($updatedEntriesIndexes as $updatedEntryIndex) {//Deal with new structure: 3 - Update all entries that has any difference between original and new array (except the entries that has original empty). Be sure to add '_' as suffix of area field value;
                    $willBeUpdatedEntryRef = &$ddmPluginNavigationFromPost[$updatedEntryIndex];
                    $willBeUpdatedOriginalEntryRef = &$willBeUpdatedEntryRef['original'];
                    $willBeUpdatedOriginalEntryOrder = $willBeUpdatedOriginalEntryRef['order'];
                    $willBeUpdatedOriginalEntryArea = $willBeUpdatedOriginalEntryRef['area'];
                    if($willBeUpdatedOriginalEntryOrder===null||$willBeUpdatedOriginalEntryOrder===''||empty($willBeUpdatedOriginalEntryArea)) {
                        unset($updatedEntryIndex,$willBeUpdatedOriginalEntryOrder,$willBeUpdatedOriginalEntryArea);
                        throw new Exception('One of dropdown plugin HTTP Post array entries has invalid structure - Original data with empty order or empty area');
                    }
                    $willBeUpdatedNewEntryRef = &$willBeUpdatedEntryRef['new'];
                    $willBeUpdatedSettingNavigation = $settingNavigationRepository->findOneBy(array('order'=>$willBeUpdatedOriginalEntryOrder, 'area'=>$willBeUpdatedOriginalEntryArea));
                    if($willBeUpdatedSettingNavigation===null && $willBeUpdatedOriginalEntryArea==='header_dropdown'){//Needed to work with legacy data at first time
                        $willBeUpdatedSettingNavigation = $settingNavigationRepository->findOneBy(array('order'=>$willBeUpdatedOriginalEntryOrder, 'area'=>'header'));
                    }
                    if($willBeUpdatedSettingNavigation===null){//If can not find navigation entry on database, create a new one
                        $willBeUpdatedSettingNavigation = new SettingNavigation();
                        $willBeUpdatedSettingNavigation->setCustom('1');
                        $willBeUpdatedSettingNavigation->setPage(null);
                        $willBeUpdatedSettingNavigation->setLink('');
                    }

                    $willBeUpdatedSettingNavigation->setOrder(intval($willBeUpdatedNewEntryRef['order']));
                    $willBeUpdatedSettingNavigation->setArea($willBeUpdatedNewEntryRef['area'].'_temp');
                    $willBeUpdatedSettingNavigation->setLabel($willBeUpdatedNewEntryRef['label']);
                    $willBeUpdatedSettingNavigation->setCustom($willBeUpdatedNewEntryRef['custom']);
                    $pageIdExistsOnOriginal = array_key_exists('pageId', $willBeUpdatedOriginalEntryRef)&&$willBeUpdatedOriginalEntryRef['pageId']!==null&&$willBeUpdatedOriginalEntryRef['pageId']!=='';
                    $pageIdExistsOnNew = array_key_exists('pageId', $willBeUpdatedNewEntryRef)&&$willBeUpdatedNewEntryRef['pageId']!==null&&$willBeUpdatedNewEntryRef['pageId']!=='';
                    $hasChangedPageId = !(($pageIdExistsOnOriginal === $pageIdExistsOnNew) && ((!$pageIdExistsOnNew && !$pageIdExistsOnOriginal) || ($willBeUpdatedOriginalEntryRef['pageId'] === $willBeUpdatedNewEntryRef['pageId'])));

                    if($hasChangedPageId) {
                        if(!$pageIdExistsOnNew){
                            $willBeUpdatedSettingNavigation->setPage(null);
                        } elseif(is_numeric($willBeUpdatedNewEntryRef['pageId']) && !empty($willBeUpdatedNewEntryRef['pageId'])) {
                            $page = $pageRepository->find($willBeUpdatedNewEntryRef['pageId']);
                            if($page!==null){
                                $willBeUpdatedSettingNavigation->setPage($page);
                            } else {//If for some reason the page cannot be found, convert to a empty custom link
                                $willBeUpdatedSettingNavigation->setCustom('1');
                                $willBeUpdatedSettingNavigation->setPage(null);
                            }
                            unset($page);
                        } else {//If for some reason the page id received is invalid, convert to a empty custom link
                            $willBeUpdatedSettingNavigation->setCustom('1');
                            $willBeUpdatedSettingNavigation->setPage(null);
                        }
                    }
                    unset($pageIdExistsOnOriginal,$pageIdExistsOnNew,$hasChangedPageId);
                    $linkExistsOnOriginal = array_key_exists('link', $willBeUpdatedOriginalEntryRef)&&!empty($willBeUpdatedOriginalEntryRef['link']);
                    $linkExistsOnNew = array_key_exists('link', $willBeUpdatedNewEntryRef)&&!empty($willBeUpdatedNewEntryRef['link']);
                    $hasChangedLink = !(($linkExistsOnOriginal === $linkExistsOnNew) && ((!$linkExistsOnOriginal&&!$linkExistsOnNew) || ($willBeUpdatedOriginalEntryRef['link'] === $willBeUpdatedNewEntryRef['link'])));
                    if($hasChangedLink) {
                        if(!$linkExistsOnNew){
                            $willBeUpdatedSettingNavigation->setLink('');
                        } else {
                            $willBeUpdatedSettingNavigation->setLink($willBeUpdatedNewEntryRef['link']);
                        }
                    }
                    unset($linkExistsOnOriginal,$linkExistsOnNew,$hasChangedLink);
                    $em->persist($willBeUpdatedSettingNavigation);
                    $needFlush = true;
                }
                unset($updatedEntriesIndexes,$updatedEntryIndex);

                foreach($createdEntriesIndexes as $createdEntryIndex) {//Deal with new structure: 4 - Create all entries that has original empty and wasn't marked as deleted on new array. Be sure to add '_' as suffix of area field value;
                    $willBeCreatedEntryRef = &$ddmPluginNavigationFromPost[$createdEntryIndex];
                    $willBeCreatedNewEntryRef = &$willBeCreatedEntryRef['new'];
                    $willBeCreatedNewEntryOrder = $willBeCreatedNewEntryRef['order'];
                    $willBeCreatedNewEntryArea = $willBeCreatedNewEntryRef['area'];
                    $willBeCreatedSettingNavigation = $settingNavigationRepository->findOneBy(array('order'=>$willBeCreatedNewEntryOrder, 'area'=>$willBeCreatedNewEntryArea));//If for any reason the registry already exists, use it
                    if($willBeCreatedSettingNavigation===null){//If can not find navigation entry on database, create a new one
                        $willBeCreatedSettingNavigation = new SettingNavigation();
                    }
                    $willBeCreatedSettingNavigation->setOrder(intval($willBeCreatedNewEntryRef['order']));
                    $willBeCreatedSettingNavigation->setArea($willBeCreatedNewEntryRef['area'].'_temp');
                    $willBeCreatedSettingNavigation->setLabel($willBeCreatedNewEntryRef['label']);
                    $willBeCreatedSettingNavigation->setCustom($willBeCreatedNewEntryRef['custom']);
                    $pageIdExistsOnNew = array_key_exists('pageId', $willBeCreatedNewEntryRef)&&$willBeCreatedNewEntryRef['pageId']!==null&&$willBeCreatedNewEntryRef['pageId']!=='';
                    if(!$pageIdExistsOnNew){
                        $willBeCreatedSettingNavigation->setPage(null);
                    } elseif(is_numeric($willBeCreatedNewEntryRef['pageId']) && !empty($willBeCreatedNewEntryRef['pageId'])) {
                        $page = $pageRepository->find($willBeCreatedNewEntryRef['pageId']);
                        if($page!==null){
                            $willBeCreatedSettingNavigation->setPage($page);
                        } else {//If for some reason the page cannot be found, convert to a empty custom link
                            $willBeCreatedSettingNavigation->setCustom('1');
                            $willBeCreatedSettingNavigation->setPage(null);
                        }
                        unset($page);
                    } else {//If for some reason the page id received is invalid, convert to a empty custom link
                        $willBeCreatedSettingNavigation->setCustom('1');
                        $willBeCreatedSettingNavigation->setPage(null);
                    }

                    unset($pageIdExistsOnNew,$hasChangedPageId);
                    $linkExistsOnNew = array_key_exists('link', $willBeCreatedNewEntryRef)&&!empty($willBeCreatedNewEntryRef['link']);

                    if(!$linkExistsOnNew){
                        $willBeCreatedSettingNavigation->setLink('');
                    } else {
                        $willBeCreatedSettingNavigation->setLink($willBeCreatedNewEntryRef['link']);
                    }

                    unset($linkExistsOnNew,$hasChangedLink);
                    $em->persist($willBeCreatedSettingNavigation);
                    $needFlush = true;
                }
                unset($createdEntriesIndexes, $createdEntryIndex);

                foreach($deletedChildEntriesIndexes as $deletedChildEntryIndex) {//Deal with new structure: 5 - Delete all child entries that has difference between original and new array relative to order (except the entries that has original empty).;
                    $willBeDeletedEntryRef = &$ddmPluginNavigationFromPost[$deletedChildEntryIndex];
                    $willBeDeletedOriginalEntryRef = &$willBeDeletedEntryRef['original'];
                    $willBeDeletedEntryOrder = $willBeDeletedOriginalEntryRef['order'];
                    $willBeDeletedSettingNavigationDropdownMenu = $settingNavigationDropdownMenuRepository->find($willBeDeletedEntryOrder);
                    if($willBeDeletedSettingNavigationDropdownMenu!==null){
                        $em->remove($willBeDeletedSettingNavigationDropdownMenu);
                        $needFlush = true;
                    }
                    unset($willBeDeletedEntryOrder,$willBeDeletedSettingNavigationDropdownMenu);
                }
                unset($deletedChildEntriesIndexes,$deletedChildEntryIndex);

                if($needFlush){
                    $em->flush();//Needed to avoid conflicts with non persisted deletions with create SettingNavigationDropdownMenu entities block
                    $needFlush = false;
                }

                foreach($updatedChildEntriesIndexes as $updatedChildEntryIndex) {//Deal with new structure: 6 - Update all child entries that has any difference between original and new array (except the entries that has original empty).;
                    $willBeUpdatedEntryRef = &$ddmPluginNavigationFromPost[$updatedChildEntryIndex];
                    $willBeUpdatedOriginalEntryRef = &$willBeUpdatedEntryRef['original'];
                    $willBeUpdatedNewEntryRef = &$willBeUpdatedEntryRef['new'];
                    $parentIdExistsOnOriginal = array_key_exists('parentId', $willBeUpdatedOriginalEntryRef)&&$willBeUpdatedOriginalEntryRef['parentId']!==null&&$willBeUpdatedOriginalEntryRef['parentId']!=='';
                    $parentIdExistsOnNew = array_key_exists('parentId', $willBeUpdatedNewEntryRef)&&$willBeUpdatedNewEntryRef['parentId']!==null&&$willBeUpdatedNewEntryRef['parentId']!=='';
                    $hasChangedParentId = !(($parentIdExistsOnOriginal === $parentIdExistsOnNew) && ((!$parentIdExistsOnOriginal && !$parentIdExistsOnNew) || ($willBeUpdatedOriginalEntryRef['parentId'] === $willBeUpdatedNewEntryRef['parentId'])));
                    if($hasChangedParentId){
                        $willBeUpdatedOriginalEntryOrder = $willBeUpdatedOriginalEntryRef['order'];
                        $willBeUpdatedNewEntryOrder = $willBeUpdatedNewEntryRef['order'];
                        if($parentIdExistsOnOriginal&&$parentIdExistsOnNew){
                            $willBeUpdatedOriginalEntryParentId = $willBeUpdatedOriginalEntryRef['parentId'];
                            if($willBeUpdatedOriginalEntryParentId===null||$willBeUpdatedOriginalEntryParentId===''||!is_numeric($willBeUpdatedOriginalEntryParentId)) {
                                unset($updatedChildEntryIndex,$parentIdExistsOnOriginal,$parentIdExistsOnNew,$hasChangedParentId,$willBeUpdatedOriginalEntryOrder,$willBeUpdatedOriginalEntryParentId);
                                throw new Exception('One of dropdown plugin HTTP Post array entries has invalid structure - Original data with invalid parent menu reference');
                            }
                            $willBeUpdatedNewEntryParentId = $willBeUpdatedNewEntryRef['parentId'];
                            if($willBeUpdatedNewEntryParentId===null||$willBeUpdatedNewEntryParentId===''||!is_numeric($willBeUpdatedNewEntryParentId)) {
                                unset($updatedChildEntryIndex,$parentIdExistsOnOriginal,$parentIdExistsOnNew,$hasChangedParentId,$willBeUpdatedOriginalEntryOrder,$willBeUpdatedOriginalEntryParentId,$willBeUpdatedNewEntryParentId);
                                throw new Exception('One of dropdown plugin HTTP Post array entries has invalid structure - New data with invalid parent menu reference');
                            }
                            $willBeUpdatedSettingNavigationDropdownMenu = $settingNavigationDropdownMenuRepository->findOneBy(array('id'=>$willBeUpdatedNewEntryOrder,'parentMenu'=>$willBeUpdatedNewEntryParentId));
                            if($willBeUpdatedSettingNavigationDropdownMenu===null) {
                                $willBeUpdatedSettingNavigationDropdownMenu = new SettingNavigationDropdownMenu();
                            }
                            $willBeUpdatedSettingNavigationDropdownMenu->setId($willBeUpdatedNewEntryOrder);
                            $willBeUpdatedSettingNavigationDropdownMenu->setParentMenu($willBeUpdatedNewEntryParentId);
                            $em->persist($willBeUpdatedSettingNavigationDropdownMenu);
                            $needFlush = true;
                        }
                    }
                }
                unset($updatedChildEntriesIndexes,$updatedChildEntryIndex);

                foreach($createdChildEntriesIndexes as $createdChildEntryIndex) {//Deal with new structure: 7 - Create all child entries that has original empty and wasn't marked as deleted on new array;
                    $willBeCreatedEntryRef = &$ddmPluginNavigationFromPost[$createdChildEntryIndex];
                    $willBeCreatedNewEntryRef = &$willBeCreatedEntryRef['new'];
                    $willBeCreatedNewEntryOrder = $willBeCreatedNewEntryRef['order'];
                    $parentIdExistsOnNew = array_key_exists('parentId', $willBeCreatedNewEntryRef)&&$willBeCreatedNewEntryRef['parentId']!==null&&$willBeCreatedNewEntryRef['parentId']!=='';
                    $willBeCreatedNewEntryParentId = $willBeCreatedNewEntryRef['parentId'];
                    if($parentIdExistsOnNew && ($willBeCreatedNewEntryParentId===null||$willBeCreatedNewEntryParentId===''||!is_numeric($willBeCreatedNewEntryParentId))) {
                        unset($createdChildEntryIndex,$willBeCreatedNewEntryOrder,$parentIdExistsOnNew,$willBeCreatedNewEntryParentId);
                        throw new Exception('One of dropdown plugin HTTP Post array entries has invalid structure - New data with invalid parent menu reference');
                    }
                    $willBeCreatedSettingNavigationDropdownMenu = $settingNavigationDropdownMenuRepository->find($willBeCreatedNewEntryOrder);
                    if($willBeCreatedSettingNavigationDropdownMenu===null) {
                        $willBeCreatedSettingNavigationDropdownMenu = new SettingNavigationDropdownMenu();
                    }
                    $willBeCreatedSettingNavigationDropdownMenu->setId($willBeCreatedNewEntryOrder);
                    $willBeCreatedSettingNavigationDropdownMenu->setParentMenu($willBeCreatedNewEntryParentId);
                    $em->persist($willBeCreatedSettingNavigationDropdownMenu);
                    $needFlush = true;
                }
                unset($createdChildEntriesIndexes, $createdChildEntryIndex);

                //Deal with new structure: 8 - Execute the started transaction
                if($needFlush){
                    $em->flush();
                }
                $em->commit();
            } catch (Exception $transactExcept) {
                $em->rollBack();
                $em->close();
                throw $transactExcept;
            }
            try {
                $em->beginTransaction();
                $needFlush = false;
                //Deal with new structure: 6 - If transaction succeded, execute a update in all database entries that has area field value with suffix '_', removing this suffix of the field values;
                $qb = $settingNavigationRepository->createQueryBuilder('sn');
                if ($qb !== null) {
                    /** @var SettingNavigation[] $menuItensWithTempArea */
                    $menuItensWithTempArea = $qb->select('sn')
                        ->where($qb->expr()->like('sn.area', $qb->expr()->literal('%_temp')))
                        ->getQuery()
                        ->getResult();

                    foreach ($menuItensWithTempArea as $menuItemWithTempArea) {
                        $areaChanged = false;
                        $tempArea = $menuItemWithTempArea->getArea();
                        if (substr($tempArea, -5, 5) === '_temp') {
                            $menuItemWithTempArea->setArea(substr($tempArea, 0, -5));
                            $areaChanged = true;
                        }
                        if($areaChanged){
                            $em->persist($menuItemWithTempArea);
                            $needFlush = true;
                        }
                        unset($areaChanged, $tempArea);
                    }
                    unset($menuItensWithTempArea,$menuItensWithTempArea,$menuItemWithTempArea);
                }
                unset($qb);
                if($needFlush){
                    $em->flush();
                }
                $em->commit();
                $returnValue = true;
            } catch (Exception $updateAreaExcept){
                $em->rollBack();
                throw $updateAreaExcept;
            }
        } catch (Exception $e) {
            if ($logger !== null) {
                $logger->critical('Unexpected error on tryToSaveNavigationForm method of NavigationDropdownMenuService.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
            $errorMessage = $this->getTranslatedString('Unexpected error when trying to save site navigation.',true);
            $returnValue = false;
        } finally {
            unset($logger);
            if ($notLoggedCriticalException !== null) {
                throw $notLoggedCriticalException;
            }
        }
        return $returnValue;
    }

    public function tryToBuildNavigationFromPost($postArray)
    {
        return array();
    }

    /**
     * Method just to allow JMSTranslator include know strings that was not used directly by a trans method call or by a trans twig extension
     */
    private function dummyMethodToIncludeTranslatableString(){
        return;
            $this->translatorService->trans('Empty menu label is not allowed. The last valid value has been restored.', array(), 'messages');
            $this->translatorService->trans('\'Not associated\' menu label is not allowed. The last valid value has been restored.', array(), 'messages');
            $this->translatorService->trans('New menu', array(), 'messages');
            $this->translatorService->trans('Unexpected error on reset navigation.', array(), 'messages');
            $this->translatorService->trans('Unexpected error on save navigation.', array(), 'messages');
            $this->translatorService->trans('Reloading previous saved navigation.', array(), 'messages');
            $this->translatorService->trans('Unexpected error on load navigation.', array(), 'messages');
            $this->translatorService->trans('Unexpected error on after rebuild navigation due to an empty navigation data.', array(), 'messages');
            $this->translatorService->trans('Unable to display navigation edit form due to an unexpected behavior.', array(),'messages');
            $this->translatorService->trans('Menu item hidden on front-end', array(), 'messages');
            $this->translatorService->trans('Menu item appears on front-end', array(), 'messages');
            $this->translatorService->trans('Are you sure you would like to remove this menu item, including all sub menus related to it: ', array(), 'messages');
            $this->translatorService->trans('Navigation menu configurations', array(),'system');
            $this->translatorService->trans('Configure below your site navigation menu. You can change or delete actual ones, add new ones, reorder then and also create sub-menus.', array(), 'messages');
            $this->translatorService->trans('Menu with sub menus', array(), 'system');
            $this->translatorService->trans('Navigation menu', array(),'system');
            $this->translatorService->trans('Configure navigation menu for your site', array(),'system');
            $this->translatorService->trans('Reset navigation menu', array(),'system');
            $this->translatorService->trans('Association', array(), 'system');
            $this->translatorService->trans('Label', array(), 'system');
            $this->translatorService->trans('Visibility', array(), 'system');
            $this->translatorService->trans('Links to', array(), 'system');
            $this->translatorService->trans('Unavailable page', array(), 'system');
            $this->translatorService->trans('Unav.', array(), 'system');
            $this->translatorService->trans('Not associated', array(), 'system');
            $this->translatorService->trans('Separate this sub menu', array(),'system');
            $this->translatorService->trans('Separate all sub menus', array(),'system');
            $this->translatorService->trans('Add menu item', array(),'system');
    }

    /**
     * @return false|string
     * @throws Exception
     */
    public function getSitemanagerLang(){
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        $returnValue = 'en';
        if(empty($this->sitemanagerLang)) {
            try {
                if (!empty($this->mainSettings)) {
                    $sitemgrLocale = $this->mainSettings->getSetting('sitemgr_language');
                    $sitemgrIsoLang = $this->languageHandler->getISOLang($sitemgrLocale);
                    $siteIsoLangPreffix = substr($sitemgrIsoLang, 0, 2);
                    if ($siteIsoLangPreffix !== false) {
                        $returnValue = $siteIsoLangPreffix;
                    }
                    unset($sitemgrLocale, $sitemgrIsoLang);
                }

            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getSitemanagerLang method of NavigationDropdownMenuService.php, when getting site manager language', ['exception' => $e]);
                } else {
                    throw $e;
                }
            }
            $this->sitemanagerLang = $returnValue;
        } else {
            $returnValue = $this->sitemanagerLang;
        }
        return $returnValue;
    }

    /**
     * @param $string
     * @param $isSiteManager
     * @param string $translateDomain
     * @return string
     */
    public function getTranslatedString($string, $isSiteManager, $translateDomain = 'messages')
    {
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $lang = 'en';
            if (!$isSiteManager) {
                if (empty($this->domainLang)) {
                    try {
                        if (!empty($this->multiDomainSettings)) {
                            $domainLocale = $this->multidomainSettings->getLocale();
                            $domainIsoLang = $this->languageHandler->getISOLang($domainLocale);
                            $domainIsoLangPreffix = substr($domainIsoLang, 0, 2);
                            if ($domainIsoLangPreffix !== false) {
                                $lang = $domainIsoLangPreffix;
                            }
                            unset($domainLocale, $domainIsoLang);
                        }
                    } catch (Exception $e) {
                        if ($logger !== null) {
                            $logger->critical('Unexpected error on getTranslatedString method of NavigationDropdownMenuService.php, when getting front-end language', ['exception' => $e]);
                        } else {
                            throw $e;
                        }
                    }
                    $this->domainLang = $lang;
                } else {
                    $lang = $this->domainLang;
                }
            } else {
                $lang = $this->getSitemanagerLang();
            }

            return $this->translatorService->trans(/** @Ignore */$string, array(), $translateDomain, $lang);
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on getTranslatedString method of NavigationDropdownMenuService.php', ['exception' => $e]);
            }
            return $string;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function resetNavigation()
    {
        $resetFailed = false;
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            if ($this->doctrine !== null) {
                /** @var SettingNavigationRepository $settingNavigationRepository */
                $settingNavigationRepository = $this->doctrine->getRepository('WebBundle:SettingNavigation');
                /** @var DropdownMenuRepository $settingNavigationDropdownMenu */
                $settingNavigationDropdownMenu = $this->doctrine->getRepository('DropdownMenuBundle:SettingNavigationDropdownMenu');
                /** @var PageRepository $pageRepository */
                $pageRepository = $this->doctrine->getRepository('WysiwygBundle:Page');
                /** @var EntityManagerInterface $em */
                $em = $this->doctrine->getManager();
                $needFlush = false;
                if ($pageRepository!==null && $settingNavigationRepository !== null && $settingNavigationDropdownMenu !== null && $em !== null) {
                    $settingNavigationDropdownMenuItems = $settingNavigationDropdownMenu->findAll();
                    foreach ($settingNavigationDropdownMenuItems as $settingNavigationDropdownMenuItem) {
                        $em->remove($settingNavigationDropdownMenuItem);
                        $needFlush = true;
                    }
                    $qb = $settingNavigationRepository->createQueryBuilder('sn');
                    if($qb!==null) {
                        $settingNavigationRepositoryItems = $qb->select('sn')
                            ->where($qb->expr()->like('sn.area', $qb->expr()->literal('header%')))
                            ->getQuery()
                            ->getResult();
                        foreach ($settingNavigationRepositoryItems as $settingNavigationRepositoryItem) {
                            $em->remove($settingNavigationRepositoryItem);
                            $needFlush = true;
                        }
                        unset($settingNavigationRepositoryItems, $settingNavigationRepositoryItem);
                    }
                    unset($qb);
                    if ($needFlush) {
                        $em->flush();
                        $needFlush = false;
                    }

                    /* These are the standard data of the system */
                    $standardHeaderInserts = [
                        [
                            'order'  => 0,
                            'label'  => $this->getTranslatedString('Home', false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::HOME_PAGE)
                        ],
                        [
                            'order'  => 1,
                            'label'  => $this->getTranslatedString('Listings',false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::LISTING_HOME_PAGE)
                        ],
                        [
                            'order'  => 2,
                            'label'  => $this->getTranslatedString('Events',false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::EVENT_HOME_PAGE)
                        ],
                        [
                            'order'  => 3,
                            'label'  => $this->getTranslatedString('Classifieds',false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::CLASSIFIED_HOME_PAGE)
                        ],
                        [
                            'order'  => 4,
                            'label'  => $this->getTranslatedString('Articles',false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::ARTICLE_HOME_PAGE)
                        ],
                        [
                            'order'  => 5,
                            'label'  => $this->getTranslatedString('Deals',false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::DEAL_HOME_PAGE)
                        ],
                        [
                            'order'  => 6,
                            'label'  => $this->getTranslatedString('Blog',false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::BLOG_HOME_PAGE)
                        ],
                        [
                            'order'  => 7,
                            'label'  => $this->getTranslatedString('Advertise',false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::ADVERTISE_PAGE)
                        ],
                        [
                            'order'  => 8,
                            'label'  => $this->getTranslatedString('Contact us', false),
                            'link'   => '',
                            'area'   => 'header',
                            'custom' => 0,
                            'page'   => $pageRepository->getPageByType(PageType::CONTACT_US_PAGE)
                        ],
                    ];

                    for ($i = 0, $iMax = count($standardHeaderInserts); $i < $iMax; $i++) {
                        $newSettingNavigationRepositoryItem = new SettingNavigation();
                        $newSettingNavigationRepositoryItem->setArea($standardHeaderInserts[$i]['area']);
                        $newSettingNavigationRepositoryItem->setOrder(($standardHeaderInserts[$i]['order']+1)*100);
                        $newSettingNavigationRepositoryItem->setLabel($standardHeaderInserts[$i]['label']);
                        $newSettingNavigationRepositoryItem->setLink($standardHeaderInserts[$i]['link']);
                        $newSettingNavigationRepositoryItem->setCustom($standardHeaderInserts[$i]['custom']);
                        $newSettingNavigationRepositoryItem->setPage($standardHeaderInserts[$i]['page']);

                        $em->persist($newSettingNavigationRepositoryItem);
                        $needFlush = true;
                    }
                    unset($standardHeaderInserts);
                    if ($needFlush) {
                        $em->flush();
                    }
                } else {
                    $resetFailed = true;
                }
                unset($settingNavigationRepository, $settingNavigationDropdownMenu,$pageRepository, $em);
            } else {
                $resetFailed = true;
            }
        } catch (Exception $e) {
            $resetFailed = true;
            if ($logger !== null) {
                $logger->critical('Unexpected error on resetNavigation method of NavigationDropdownMenuService.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
        } finally {
            unset($logger);
            if ($notLoggedCriticalException !== null) {
                throw $notLoggedCriticalException;
            }
        }
        return !$resetFailed;
    }
}
