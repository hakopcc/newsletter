<?php

namespace Application\Migrations;

use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Entity\SettingNavigationDropdownMenu;
use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Repository\DropdownMenuRepository;
use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Services\NavigationDropdownMenuService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Entity\SettingNavigation;
use ArcaSolutions\WebBundle\Repository\SettingNavigationRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class VersionDropdownMenu5 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $_container;

    /**
     * @inheritDoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->_container = $container;
    }

    /**
     * @inheritDoc
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     * @throws DBALException
     * @throws Exception
     */
    public function up(Schema $schema)
    {
        $container = $this->getContainer();
        if($container === null) {
            unset($container);
            throw new Exception('Can not get symfony container.');
        }
        /** @var NavigationDropdownMenuService $ddmPluginNavigationService */
        $ddmPluginNavigationService = $container->get('ddm_plugin.navigation.service');
        if($ddmPluginNavigationService === null) {
            unset($ddmPluginNavigationService);
            throw new Exception('Can not get Dropdown Menu Plugin navigation service.');
        }
        $navigationItems = $ddmPluginNavigationService->getHeader(true, true, true);
        /** @var DoctrineRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        if($doctrine === null) {
            unset($doctrine);
            throw new Exception('Can not get doctrine interface.');
        }

        /** @var SettingNavigationRepository $settingNavigationRepository */
        $settingNavigationRepository = $doctrine->getRepository('WebBundle:SettingNavigation');
        /** @var DropdownMenuRepository $settingNavigationDropdownMenu */
        $settingNavigationDropdownMenuRepository = $doctrine->getRepository('DropdownMenuBundle:SettingNavigationDropdownMenu');
        if($settingNavigationRepository===null || $settingNavigationDropdownMenuRepository===null){
            unset($settingNavigationRepository,$settingNavigationDropdownMenuRepository);
            throw new Exception('Can not get Doctrine SettingNavigation repository or SettingNavigationDropdownMenu repository.');
        }

        /** @var EntityManagerInterface $em */
        $em = $doctrine->getManager();
        if ($em === null) {
            unset($ddmPluginNavigationFromPost, $em);
            throw new Exception('Can not get Doctrine Entity Manager.');
        }

        $parentItemsIdArray = array();
        $childItemsIdArray = array();
        try {
            $needFlush = false;
            $em->beginTransaction(); // Deal with new structure: 1 - Start a new transaction (suspend auto-commit)
            $initialOrderIndex = 0;
            foreach ($navigationItems as $navigationItem) {
                $targetMenuInputNewOrderValue = ($initialOrderIndex + 1) * 100;
                /** @var SettingNavigation $targetMenu */
                $targetMenu = $settingNavigationRepository->findOneBy(array('order'=>$navigationItem['order'], 'area'=>$navigationItem['area']));
                if($targetMenu===null && $navigationItem['area']==='header_dropdown') {
                    $targetMenu = $settingNavigationRepository->findOneBy(array('order'=>$navigationItem['order'], 'area'=>'header'));
                }
                if($targetMenu!==null) {
                    $targetMenu->setOrder($targetMenuInputNewOrderValue);
                    if ($navigationItem['area'] === 'header_dropdown' && !empty($navigationItem['children']) && is_array($navigationItem['children'])) {
                        $targetMenu->setArea('header_dropdown');
                        $targetMenu->setLink('');
                        $em->persist($targetMenu);
                        $needFlush = true;
                        $parentItemsIdArray[] = $targetMenuInputNewOrderValue;
                        $initialOrderChildIndex = 1;
                        foreach ($navigationItem['children'] as $navigationChildItem) {
                            $targetSubMenuInputNewOrderValue = $targetMenuInputNewOrderValue + $initialOrderChildIndex;
                            $targetSubMenu = $settingNavigationRepository->findOneBy(array('order' => $navigationChildItem['order'], 'area' => $navigationChildItem['area']));
                            if($targetSubMenu===null && $navigationChildItem['area']==='header_dropdown') {
                                $targetSubMenu = $settingNavigationRepository->findOneBy(array('order'=>$navigationChildItem['order'], 'area'=>'header'));
                            }
                            if($targetSubMenu!==null) {
                                $targetSubMenu->setOrder($targetSubMenuInputNewOrderValue);
                                $targetSubMenu->setArea('header_dropdown');
                                if(strtolower(trim($targetSubMenu->getLink())) ==='null'){
                                    $targetSubMenu->setLink('');
                                }
                                $em->persist($targetSubMenu);
                                $existingTargetSettingNavigationDropdownMenu = $settingNavigationDropdownMenuRepository->findOneBy(array('id'=>$navigationChildItem['order'],'parentMenu'=>$navigationItem['order']));
                                if($existingTargetSettingNavigationDropdownMenu!==null) {
                                    $em->remove($existingTargetSettingNavigationDropdownMenu);
                                }
                                $targetSettingNavigationDropdownMenu = new SettingNavigationDropdownMenu();
                                $targetSettingNavigationDropdownMenu->setId($targetSubMenuInputNewOrderValue);
                                $targetSettingNavigationDropdownMenu->setParentMenu($targetMenuInputNewOrderValue);
                                $em->persist($targetSettingNavigationDropdownMenu);
                                $needFlush = true;
                                $childItemsIdArray[] = $targetSubMenuInputNewOrderValue;
                            }
                            $initialOrderChildIndex++;
                        }
                    } else {
                        if(strtolower(trim($targetMenu->getLink())) ==='null'){
                            $targetMenu->setLink('');
                        }
                        $em->persist($targetMenu);
                        $needFlush = true;
                        $parentItemsIdArray[] = $targetMenuInputNewOrderValue;
                    }
                    $initialOrderIndex++;
                }
            }

            if ($needFlush) {
                $em->flush();
            }
            $em->commit();
            echo 'Site navigation successfully migrated'.PHP_EOL;
        } catch (Exception $transactExcept) {
            $em->rollBack();
            $em->close();
            throw $transactExcept;
        } finally {
            unset($em, $doctrine, $navigationItems, $ddmPluginNavigationService, $container);
        }
        /** @var QueryBuilder $qb */
        $qb = $settingNavigationDropdownMenuRepository->createQueryBuilder('snd');
        if ($qb !== null) {
            $whereOrConditions = array();
            $whereOrConditions[] = $qb->expr()->isNull('snd.parentMenu');
            if(!empty($childItemsIdArray)){
                $whereOrConditions[] = $qb->expr()->notIn('snd.id', ':childItems');
            }
            if (!empty($parentItemsIdArray)){
                $whereOrConditions[] = $qb->expr()->notIn('snd.parentMenu', ':parentItems');
            }
            if(!empty($whereOrConditions)) {
                if (count($whereOrConditions) > 1) {
                    $whereExpression = $qb->expr()->orX()->addMultiple($whereOrConditions);
                } else {
                    $whereExpression = $whereOrConditions[0];
                }
                $qb = $qb->delete()->where($whereExpression);
                if (!empty($parentItemsIdArray)) {
                    $qb = $qb->setParameter('parentItems', $parentItemsIdArray);
                }
                if (!empty($childItemsIdArray)) {
                    $qb = $qb->setParameter('childItems', $childItemsIdArray);
                }
                $deleteGhostSettingNavigationDropdownMenuQuery = $qb->getQuery();
                $deleteGhostSettingNavigationDropdownMenuResult = $deleteGhostSettingNavigationDropdownMenuQuery->execute();
                echo ($deleteGhostSettingNavigationDropdownMenuResult ? 'Cleaned-up some ghost child navigation items' : 'No ghost child navigation items to cleanup.') . PHP_EOL;
                unset($deleteGhostSettingNavigationDropdownMenuQuery, $deleteGhostSettingNavigationDropdownMenuResult);
            }
        }
        unset($qb);
    }

    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     * @throws DBALException
     */
    public function down(Schema $schema)
    {
        //Theres not needed to do ("upped" version is compatible with the non-plugin version of site navigation)
    }
}
