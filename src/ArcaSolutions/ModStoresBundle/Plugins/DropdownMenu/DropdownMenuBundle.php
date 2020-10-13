<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\CoreBundle\Services\Modules;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Entity\SettingNavigationDropdownMenu;
use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Repository\DropdownMenuRepository;
use ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Services\NavigationDropdownMenuService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Entity\SettingNavigation;
use ArcaSolutions\WebBundle\Repository\SettingNavigationRepository;
use ArcaSolutions\WebBundle\Services\NavigationService;
use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use ArcaSolutions\WysiwygBundle\Entity\Theme;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use ArcaSolutions\WysiwygBundle\Repository\PageRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class DropdownMenuBundle extends Bundle
{
    private $devEnvironment = false;
    private $_siteMgrLang = 'en';
    private $_frontendLang = 'en';

    /**
     * Boots the Bundle.
     */
    public function boot()
    {
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $this->devEnvironment = Kernel::ENV_DEV === $this->container->getParameter('kernel.environment');
            try {
                /**
                 * @var LanguageHandler $languageHandler
                 */
                $languageHandler = $this->container->get('languagehandler');
                if ($languageHandler !== null) {
                    try {
                        $multiDomainInfo = $this->container->get('multi_domain.information');
                        if (!empty($multiDomainInfo)) {
                            $domainLocale = $multiDomainInfo->getLocale();
                            $domainLang = $languageHandler->getISOLang($domainLocale);
                            $this->_frontendLang = substr($domainLang, 0, 2);
                            unset($domainLocale, $domainLang);
                        }
                        unset($multiDomainInfo);
                    } catch (Exception $e) {
                        if (!empty($logger)) {
                            $logger->critical('Unexpected error on boot method of DropdownMenuBundle.php, when getting front-end language', ['exception' => $e]);
                        } else {
                            throw $e;
                        }
                    }
                    try {
                        $settings = $this->container->get('settings');
                        if (!empty($settings)) {
                            $sitemgrLocale = $settings->getSetting('sitemgr_language');
                            $sitemgrLang = $languageHandler->getISOLang($sitemgrLocale);
                            $this->_siteMgrLang = substr($sitemgrLang, 0, 2);
                            unset($sitemgrLocale, $sitemgrLang);
                        }
                        unset($settings);
                    } catch (Exception $e) {
                        if (!empty($logger)) {
                            $logger->critical('Unexpected error on boot method of DropdownMenuBundle.php, when getting site manager language', ['exception' => $e]);
                        } else {
                            throw $e;
                        }
                    }
                }
                unset($languageHandler);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on boot method of DropdownMenuBundle.php, when getting site manager and front-end languages', ['exception' => $e]);
                } else {
                    throw $e;
                }
            }
            if ($this->isSitemgr()) {

                /*
                 * Register sitemgr only bundle hooks
                 */

                Hooks::Register('classnavigation_after_makerow', function (&$params = null) {
                    return $this->getClassNavigationAfterMakeRow($params);
                });
                Hooks::Register('classnavigation_after_insertquery', function (&$params = null) {
                    return $this->getClassNavigationAfterInsertQuery($params);
                });
                Hooks::Register('classnavigation_before_delete', function (&$params = null) {
                    return $this->getClassNavigationBeforeDelete($params);
                });
                Hooks::Register('sidebardesign_after_render_emaileditor', function (&$params = null) {
                    return $this->getSideBarDesignAfterRenderEmailEditor($params);
                });
                Hooks::Register('widgetactionajax_before_save', function (&$params = null) {
                    return $this->getWidgetActionAjaxBeforeSave($params);
                });
                Hooks::Register('navigationcode_overwrite_save', function (&$params = null) {
                    return $this->getNavigationCodeOverwriteSave($params);
                });
                Hooks::Register('sitemgrfooter_after_render_js', function (&$params = null) {
                    return $this->getSitemgrFooterAfterRenderJs($params);
                });
                Hooks::Register('legacy-functions-validate-form_before_validate', function (&$params = null){
                    $this->getLegacyFunctionsValidateFormBeforeValidate($params);
                });
                Hooks::Register('legacy-includes-modals-widget-editheadertype3_overwrite_navigation-edit', function (&$params = null) {
                    return $this->getLegacyIncludesModalsWidgetEditheaderOverwriteNavigationEdit($params);
                });
                Hooks::Register('legacy-includes-modals-widget-editheader_overwrite_navigation-edit', function (&$params = null) {
                    return $this->getLegacyIncludesModalsWidgetEditheaderOverwriteNavigationEdit($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    return $this->getSitemgrHeaderAfterRenderMetatags($params);
                });
            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('navigationhandler_before_returnheader', function (&$params = null) {
                    return $this->getNavigationHandlerBeforeReturnHeader($params);
                });
                Hooks::Register('widgetactionajax_before_save', function (&$params = null) {
                    return $this->getWidgetActionAjaxBeforeSave($params);
                });
                Hooks::Register('headermenu_overwrite_menu', function (&$params = null) {
                    return $this->getHeaderMenuOverwriteMenu($params);
                });
                Hooks::Register('navigationextension_before_render_navigationheader', function (&$params = null) {
                    return $this->getNavigationExtensionBeforeRenderNavigationHeader($params);
                });
                Hooks::Register('wysiwyg_extension_renderpage_after_render_widgets', function (&$params = null) {
                    $this->getWysiwygExtensionRenderpageAfterRenderWidgets($params);
                });
            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of DropdownMenuBundle.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
        } finally {
            unset($logger);
            if (!empty($notLoggedCriticalException)) {
                throw $notLoggedCriticalException;
            }
        }
    }

    /**
     * getWysiwygExtensionRenderpageAfterRenderWidgets - Render the instant messenger floating button in each page, except Summary and Detail ones.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getWysiwygExtensionRenderpageAfterRenderWidgets(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                /** @var PageWidget[] $pageWidgets */
                $pageWidgets = $params['page_widgets'];
                /** @var Twig_Environment $twig */
                $twig = $this->container->get('twig');
                if ($twig!==null && !empty($pageWidgets)) {
                    $pageWidgetsTypes = array_reduce($pageWidgets, function ($carry, $item) {
                        if ($item instanceof PageWidget) {
                            /** @var PageWidget $item */
                            if (empty($carry)) {
                                $carry = array();
                            }
                            /** @var Widget $widget */
                            $widget = $item->getWidget();
                            if ($widget !== null && !in_array($widget->getType(), $carry, true)) {
                                $carry[] = $widget->getType();
                            }
                        }
                        return $carry;
                    });
                    if(in_array(Widget::HEADER_TYPE, $pageWidgetsTypes)) {
                        try {
                            $twig->render('@DropdownMenu/plugin-dropdownmenu-populate-jshandler.twig', array('lang'=>$this->_frontendLang));
                        } catch (Twig_Error_Loader $e) {
                            if ($logger !== null) {
                                $logger->error("Load error on template 'plugin-dropdownmenu-populate-jshandler.twig'.", ['exception' => $e]);
                            }
                        } catch (Twig_Error_Runtime $e) {
                            if ($logger !== null) {
                                $logger->error("Runtime error on template 'plugin-dropdownmenu-populate-jshandler.twig'.", ['exception' => $e]);
                            }
                        } catch (Twig_Error_Syntax $e) {
                            if ($logger !== null) {
                                $logger->error("Syntax error on template 'plugin-dropdownmenu-populate-jshandler.twig'.", ['exception' => $e]);
                            }
                        }
                    }
                }
                unset($pageWidgets);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getWysiwygExtensionRenderpageAfterRenderWidgets method of DropdownMenuBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param null $params
     */
    private function getSitemgrHeaderAfterRenderMetatags(&$params = null)
    {
        echo "<link href='/bundles/dropdownmenu/css/site_navigation.css' rel='preload' as='style'/>".PHP_EOL;
        echo "<link href='/bundles/dropdownmenu/css/site_navigation.css' rel='stylesheet'/>".PHP_EOL;
    }

    private function getSitemgrFooterAfterRenderJs(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], '/sitemgr/design/site-navigation/index.php') !== false) {
            echo $this->container->get('twig')->render('@DropdownMenu/js/legacy-design-site-navigation-js.html.twig', array('lang'=>$this->_siteMgrLang));
        }
    }

    private function getClassNavigationAfterMakeRow(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'mobile') == false && $params['that']->area == 'header') {
            $params['that']->parent_menu = ($params['row']['parent_menu'] || $params['row']['parent_menu'] === 'NULL') ? $params['row']['parent_menu'] : ($params['that']->parent_menu ? $params['that']->parent_menu : 'NULL');
        }
    }

    private function getClassNavigationAfterInsertQuery(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'mobile') === false && $params['navigation']->getArea() === 'header') {

            $em = $this->container->get('doctrine')->getManager();
            $connection = $em->getConnection();

            $navOrder = $params['navigation']->getOrder();

            $parent_menu = is_numeric($_POST['ddm_plugin_navigation_parent_'.$navOrder]) && $_POST['ddm_plugin_ddm_plugin_navigation_linkto_'.$navOrder] != 'dropdown' ? $_POST['ddm_plugin_navigation_parent_'.$navOrder] : null;

            $statement = $connection->prepare('SELECT * FROM Setting_Navigation_DropdownMenu WHERE id = :id');
            $statement->bindValue('id', $navOrder);
            $statement->execute();

            $dropdownMenu = $statement->fetchAll();

            if(!empty($dropdownMenu)) {
                $statement = $connection->prepare('UPDATE Setting_Navigation_DropdownMenu set `parent_menu` = :parent WHERE id = :id');
                $statement->bindValue('id', $navOrder);
                $statement->bindValue('parent', $parent_menu);
                $statement->execute();
            } else {
                $statement = $connection->prepare('INSERT INTO Setting_Navigation_DropdownMenu (`id`, `parent_menu`) VALUES (:id, :parent)');
                $statement->bindValue('id', $navOrder);
                $statement->bindValue('parent', $parent_menu);
                $statement->execute();
            }
        }
    }

    private function getClassNavigationBeforeDelete(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'mobile') == false && $params['area'] == 'header') {//TODO: Verificar se é necessário considerar também area_dropdown
            $em = $this->container->get('doctrine')->getManager();
            $connection = $em->getConnection();

            $statement = $connection->prepare('DELETE FROM Setting_Navigation_DropdownMenu WHERE 1');
            $statement->execute();
        }
    }

    private function getSideBarDesignAfterRenderEmailEditor(&$params = null)
    {
        echo $this->container->get('twig')->render('DropdownMenuBundle::sitemgr-menu.html.twig',array('lang'=>$this->_siteMgrLang));
    }

    private function getWidgetActionAjaxBeforeSave(&$params = null)
    {
        if (strpos($_POST['modal'], 'footer') === false) {
            unset($_POST['navbarArr']);
        }
    }

    private function getNavigationHandlerBeforeReturnHeader(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                /** @var NavigationDropdownMenuService $ddmPluginNavigationService */
                $ddmPluginNavigationService = $this->container->get('ddm_plugin.navigation.service');
                if($ddmPluginNavigationService!==null) {
                    $params['menu'] = $ddmPluginNavigationService->getHeader();
                }
                unset($ddmPluginNavigationService);
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getNavigationHandlerBeforeReturnHeader method of DropdownMenuBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    // Todo: methods to hooks revision names

    private function getHeaderMenuOverwriteMenu(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if ($params['items']) {
                    $this->container->get('twig')->render('@DropdownMenu/plugin-dropdownmenu-populate-jshandler.twig', array('lang' => $this->_frontendLang));
                    echo $this->container->get('twig')->render('@DropdownMenu/header-navigation.html.twig', array('items' => $params['items']));
                } else {
                    $params['_return'] = false;
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getHeaderMenuOverwriteMenu method of DropdownMenuBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
                $params['_return'] = false;
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    private function getNavigationExtensionBeforeRenderNavigationHeader(&$params = null)
    {
        if (strpos($params['twigFile'], '::blocks/navigation/') !== false) {
            $params['twigFile'] = str_replace('::blocks/navigation/', 'DropdownMenuBundle::', $params['twigFile']);
        }
    }

    private function getLegacyIncludesModalsWidgetEditheaderOverwriteNavigationEdit(&$params = null)
    {
        echo $this->container->get('twig')->render('DropdownMenuBundle::legacy-includes-modals-widget-editheader_navigation-edit.html.twig', array('lang'=>$this->_siteMgrLang));
    }

    private function getLegacyIncludesModalsWidgetEditheadertype3OverwriteNavigationEdit(&$params = null)
    {
        echo $this->container->get('twig')->render('DropdownMenuBundle::legacy-includes-modals-widget-editheadertype3_navigation-edit.html.twig', array('lang'=>$this->_siteMgrLang));
    }

    private function getNavigationCodeOverwriteSave(&$params)
    {
        $em = $this->container->get('doctrine')->getManager();

        $i = $params['new_navigation']['order'];

        !empty($params['new_navigation']['page_id']) and $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($params['new_navigation']['page_id']);

        $navigation = $this->container->get('doctrine')->getRepository('WebBundle:SettingNavigation')->findOneBy([
            'order' => $i,
            'area' => 'header'
        ]);//TODO include header_dropdown

        if(empty($navigation)) {
            $navigation = new SettingNavigation();
            $navigation->setOrder($i);
        }
        $navigation->setLabel($params['new_navigation']['label']);

        $navigation->setPage(!empty($page) ? $page : null);
        $navigation->setArea($params['new_navigation']['area']);//TODO: Verificar se é necessário considerar também area_dropdown
        if($params['new_navigation']['custom']) {
            $navigation->setCustom(1);
            $navigation->setLink($params['new_navigation']['link']);
        } else {
            $navigation->setCustom(0);
            $navigation->setLink(empty($page) ? 'dropdown' : '');
        }

        $em->persist($navigation);

        $em->flush();

        $params = [
            'navigation' => $navigation
        ];

        $this->getClassNavigationAfterInsertQuery($params);
    }

    private function getLegacyFunctionsValidateFormBeforeValidate($params){
        return;//TODO: REVIEW ALL FORM VALIDATE
        $form = $params['form'];
        $array = $params['array'];
        $errorsRef = &$params['errors'];
        if($form==='ddm_plugin_navigation_form') {
            /**
             * Get option fields to validate
             */
            $errorArray = array();
            $array_options_number = explode(",", $array["ddm_plugin_order_options"]);
            if (strlen($array_options_number[0])) {
                $arrayLinksTo = array();
                for ($i = 0; $i < count($array_options_number); $i++) {
                    if (!$array["ddm_plugin_navigation_text_" . $array_options_number[$i]]) {
                        $errorArray[] = "&#149;&nbsp; " . system_showText(str_replace("[LINK_NUMBER]", ($i + 1), LANG_SITEMGR_NAVIGATION_TEXT_REQUIRED));
                    }
                    if (($array["ddm_plugin_navigation_linkto_" . $array_options_number[$i]] == "custom") && (!$array["ddm_plugin_custom_link_" . $array_options_number[$i]])) {
                        $errorArray[] = "&#149;&nbsp; " . system_showText(str_replace("[LINK_NUMBER]", ($i + 1), LANG_SITEMGR_NAVIGATION_LINK_REQUIRED));
                    }
                    if ($array["ddm_plugin_navigation_linkto_" . $array_options_number[$i]] == "---") {
                        $errorArray[] = "&#149;&nbsp; " . system_showText(str_replace("[LINK_NUMBER]", ($i + 1), LANG_SITEMGR_NAVIGATION_LINK_REQUIRED));
                    }
                    if (($array["ddm_plugin_navigation_linkto_" . $array_options_number[$i]] != "custom") && in_array($array["ddm_plugin_navigation_linkto_" . $array_options_number[$i]], $arrayLinksTo)) {
                        if ($params['array']['ddm_plugin_navigation_linkto_'.$array_options_number[$i]] != 'dropdown') {
                            $params['errorArray'][] = '&#149;&nbsp; '.system_showText(LANG_SITEMGR_NAVIGATION_REPEATED);
                        }
                    }
                    $arrayLinksTo[] = $array["ddm_plugin_navigation_linkto_" . $array_options_number[$i]];
                }
            } else {
                $errorArray[] = "&#149;&nbsp; " . system_showText(LANG_SITEMGR_NAVIGATION_EMPTY);
            }
            if (is_array($errorArray) && $errorArray[0]) {
                $errorsRef[] = "<b>" . system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS) . "</b><br />" . implode("<br />", $errorArray);
            }
        }
    }
}
