<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Internal\Answer;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Internal\Question;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\QuestionCategory;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Internal\QuestionCategory as LegacyQuestionCategory;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Repository\QuestionCategoryRepository;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\ReportQuestion;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Search\QuestionConfiguration;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services\QuestionCategoryService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services\ReportHandlerWithCommunityForum;
use ArcaSolutions\SearchBundle\Services\SearchBlock;
use ArcaSolutions\WysiwygBundle\Entity\Theme;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;
use function strlen;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommunityForumBundle extends Bundle
{
    private $devEnvironment = false;
    /**
     * Boots the Bundle.
     */
    public function boot()
    {

        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $this->devEnvironment = Kernel::ENV_DEV === $this->container->getParameter('kernel.environment');

            if ($this->isSitemgr()) {

                /*
                 * Register sitemgr only bundle hooks
                 */
                Hooks::Register('bulkupdate_after_load_modulename', function (&$params = null) {
                    return $this->getBulkUpdateAfterLoadModuleName($params);
                });
                Hooks::Register('bulkupdate_after_load_redirectroute', function (&$params = null) {
                    return $this->getBulkUpdateAfterLoadRedirectRoute($params);
                });
                Hooks::Register('formbulk_before_load_categorytree', function (&$params = null) {
                    return $this->getFormBulkBeforeLoadCategoryTree($params);
                });
                Hooks::Register('formbulk_after_load_categorytree', function (&$params = null) {
                    return $this->getFormBulkAfterLoadCategoryTree($params);
                });
                Hooks::Register('formbulk_before_render_status', function (&$params = null) {
                    return $this->getFormBulkBeforeRenderStatus($params);
                });
                Hooks::Register('formbulk_before_render_renewaldate', function (&$params = null) {
                    return $this->getFormBulkBeforeRenderRenewalDate($params);
                });
                Hooks::Register('formbulk_before_render_level', function (&$params = null) {
                    return $this->getFormBulkBeforeRenderRenewalLevel($params);
                });
                Hooks::Register('bulkupdate_before_load_deleteobject', function (&$params = null) {
                    return $this->getBulkUpdateBeforeLoadDeleteObject($params);
                });
                Hooks::Register('bulkupdate_before_load_categoryobject', function (&$params = null) {
                    return $this->getBulkUpdateBeforeLoadCategoryObject($params);
                });
                Hooks::Register('bulkupdate_before_load_updateobject', function (&$params = null) {
                    return $this->getBulkUpdateBeforeLoadUpdateObject($params);
                });
                Hooks::Register('submenucontent_after_load_modules', function (&$params = null) {
                    return $this->getSubmenuContentAfterLoadModules($params);
                });
                Hooks::Register('adminmodule_before_categoryselectquery', function (&$params = null) {
                    return $this->getAdminModuleBeforeCategorySelectQuery($params);
                });
                Hooks::Register('adminmodule_after_build_title_query', function (&$params = null) {
                    return $this->getAdminModuleAfterBuildTitleQuery($params);
                });
                Hooks::Register('adminmodule_after_build_account_query', function (&$params = null) {
                    return $this->getAdminModuleAfterBuildAccountQuery($params);
                });
                Hooks::Register('adminmodule_before_load_deleteobject', function (&$params = null) {
                    return $this->getAdminModuleBeforeLoadDeleteObject($params);
                });
                Hooks::Register('colorschemefunct_before_write_customcss', function (&$params = null) {
                    return $this->getColorSchemeFunctBeforeWriteCustomCss($params);
                });
                Hooks::Register('listmodule_before_load_itemdata', function (&$params = null) {
                    return $this->getListModuleBeforeLoadItemData($params);
                });
                Hooks::Register('listmodule_after_load_itemdata', function (&$params = null) {
                    return $this->getListModuleAfterLoadItemData($params);
                });
                Hooks::Register('listmodule_before_load_paymentinfo', function (&$params = null) {
                    return $this->getListModuleBeforeLoadPaymentInfo($params);
                });
                Hooks::Register('listmodule_before_load_renewaldate', function (&$params = null) {
                    return $this->getListModuleBeforeLoadRenewalDate($params);
                });
                Hooks::Register('modalsearchmodule_before_render_level', function (&$params = null) {
                    return $this->getModalSearchModuleBeforeRenderLevel($params);
                });
                Hooks::Register('modalsearchmodule_before_render_category', function (&$params = null) {
                    return $this->getModalSearchModuleBeforeRenderCategory($params);
                });
                Hooks::Register('modalsearchmodule_before_render_expirationdate', function (&$params = null) {
                    return $this->getModalSearchModuleBeforeRenderExpirationDate($params);
                });
                Hooks::Register('submenucontent_after_render_modulebutton', function (&$params = null) {
                    return $this->getSubMenuContentAfterRenderModuleButton($params);
                });
                Hooks::Register('viewmodule_before_render_settingbutton', function (&$params = null) {
                    return $this->getViewModuleBeforeRenderSettingButton($params);
                });
                Hooks::Register('viewmodule_after_render_description', function (&$params = null) {
                    return $this->getViewModuleAfterRenderDescription($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('navigationservice_construct', function (&$params = null) {
                    return $this->getNavigationServiceConstruct($params);
                });
                Hooks::Register('pagetype_construct', function (&$params = null) {
                    return $this->getPageTypeConstruct($params);
                });
                Hooks::Register('widget_construct', function (&$params = null) {
                    return $this->getWidgetConstruct($params);
                });
                Hooks::Register('pagewidget_construct', function (&$params = null) {
                    return $this->getPageWidgetConstruct($params);
                });
                Hooks::Register('systemfunct_after_build_constraint', function (&$params = null) {
                    return $this->getSystemFunctAfterBuildConstraint($params);
                });
                Hooks::Register('dbfunct_after_load_domaintablename', function (&$params = null) {
                    return $this->getDbFunctAfterLoadDomainTableName($params);
                });
                Hooks::Register('modalsearchmodule_after_load_modules', function (&$params = null) {
                    return $this->getModalSearchModuleAfterLoadModules($params);
                });
                Hooks::Register('dbfunct_before_load_domainsingleobject', function (&$params = null) {
                    return $this->getDbFunctBeforeLoadDomainSingleObject($params);
                });
                Hooks::Register('dbfunct_before_load_domainmultipleobject', function (&$params = null) {
                    return $this->getDbFunctBeforeLoadDomainMultipleObject($params);
                });
                Hooks::Register('dbfunct_before_load_mainmultipleobject', function (&$params = null) {
                    return $this->getDbFunctBeforeLoadMainMultipleObject($params);
                });
                Hooks::Register('listcategory_before_load_category', function (&$params = null) {
                    return $this->getListCategoryBeforeLoadCategory($params);
                });
                Hooks::Register('listmodule_after_load_modules', function (&$params = null) {
                    return $this->getListModuleAfterLoadModules($params);
                });
                Hooks::Register('listcategory_before_render_subcategoriesbutton', function (&$params = null) {
                    return $this->getListCategoryBeforeRenderSubCategoriesButton($params);
                });
                Hooks::Register('listcategory_before_render_subcategoriescounter', function (&$params = null) {
                    return $this->getListCategoryBeforeRenderSubCategoriesCounter($params);
                });
                Hooks::Register('listcategory_before_render_addsubcategoriesbutton', function (&$params = null) {
                    return $this->getListCategoryBeforeRenderAddSubCategoriesButton($params);
                });
                Hooks::Register('constants_after_load_themenaviagation', function (&$params = null) {
                    return $this->getConstantsBeforeLoadDefinesAfterLoadThemeNaviagation($params);
                });
                Hooks::Register('constants_before_load_defines', function (&$params = null) {
                    return $this->getConstantsBeforeLoadDefines($params);
                });
                Hooks::Register('pagewidgetservice_after_add_pagedefaultwidgets', function (&$params = null) {
                    return $this->getWysiwygAfterAddPageDefaultWidgets($params);
                });
                Hooks::Register('addnewwidget_after_add_widgettype', function (&$params = null) {
                    return $this->getAddNewWidgetAfterAddWidgetType($params);
                });
                Hooks::Register('synchronizacommand_after_setup_availablemodules', function (&$params = null) {
                    return $this->getSynchronizCommandAfterSetupAvailableModules($params);
                });
                Hooks::Register('synchronizacommand_after_modules_synchronize', function (&$params = null) {
                    return $this->getSynchronizaCommandAfterModulesSynchronize($params);
                });
                Hooks::Register('synchronizacommand_after_categories_synchronize', function (&$params = null) {
                    return $this->getSynchronizaCommandAfterCategoriesSynchronize($params);
                });
                Hooks::Register('modules_before_return_availablemodules', function (&$params = null) {
                    return $this->getModulesBeforeReturnAvailableModules($params);
                });
                Hooks::Register('modules_before_return_ismoduleavailable', function (&$params = null) {
                    return $this->getModulesBeforeReturnIsModuleavAilable($params);
                });
                Hooks::Register('modules_before_return_ismodule', function (&$params = null) {
                    return $this->getModulesBeforeReturnIsModule($params);
                });
                Hooks::Register('classpagebrowsing_before_retrieve_rowobject', function (&$params = null) {
                    return $this->getClassPagebrowsingBeforeRetrieveRowObject($params);
                });
                Hooks::Register('listmodule_before_render_section', function (&$params = null) {
                    return $this->getListModuleBeforeRenderSection($params);
                });
                Hooks::Register('sidebarcontent_after_render_blogitem', function (&$params = null) {
                    return $this->getSideBarContentAfterRenderBlogItem($params);
                });
                Hooks::Register('classaccount_before_delete', function (&$params = null) {
                    return $this->getClassAccountBeforeDelete($params);
                });

                Hooks::Register('enussitemgr_before_load_defines', function (&$params = null) {
                    return $this->getLanguageSitemgrBeforeLoadDefines($params, 'en');
                });
                Hooks::Register('gegesitemgr_before_load_defines', function (&$params = null) {
                    return $this->getLanguageSitemgrBeforeLoadDefines($params, 'de');
                });
                Hooks::Register('trtrsitemgr_before_load_defines', function (&$params = null) {
                    return $this->getLanguageSitemgrBeforeLoadDefines($params, 'tr');
                });
                Hooks::Register('esessitemgr_before_load_defines', function (&$params = null) {
                    return $this->getLanguageSitemgrBeforeLoadDefines($params, 'es');
                });
                Hooks::Register('ititsitemgr_before_load_defines', function (&$params = null) {
                    return $this->getLanguageSitemgrBeforeLoadDefines($params, 'it');
                });
                Hooks::Register('ptbrsitemgr_before_load_defines', function (&$params = null) {
                    return $this->getLanguageSitemgrBeforeLoadDefines($params, 'pt');
                });
                Hooks::Register('frfrsitemgr_before_load_defines', function (&$params = null) {
                    return $this->getLanguageSitemgrBeforeLoadDefines($params, 'fr');
                });


                Hooks::Register('emaileditor_after_load_defaultvars', function (&$params = null) {
                    return $this->getEmailEditorAfterLoadDefaulVars($params);
                });
                Hooks::Register('emailnotificationservice_construct', function (&$params = null) {
                    return $this->getEmailNotificationServiceConstruct($params);
                });
                Hooks::Register('categorycode_before_initialize_objectonformvalidate', function (&$params = null) {
                    return $this->getCategoryCodeBeforeInitializeObjectOnFormValidate($params);
                });
                Hooks::Register('categorycode_after_save', function (&$params = null) {
                    $this->getCategoryCodeAfterSave($params);
                });
                Hooks::Register('categorycode_before_initialize_objectonremoveimage', function (&$params = null) {
                    return $this->getCategoryCodeBeforeInitializeObjectOnRemoveImage($params);
                });
                Hooks::Register('legacy-add-mult-categories-code_after_set-category-module-table', function (&$params = null) {
                    return $this->getLegacyAddMultCategoriesCodeAfterSetCategoryModuleTable($params);
                });
                Hooks::Register('legacy-add-mult-categories-code_before_set-category-object-values', function (&$params = null) {
                    return $this->getLegacyAddMultCategoriesCodeBeforeSetCategoryObjectValues($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('formfacebooklogin_after_build_redirect', function (&$params = null) {
                    return $this->getFormFacebookLoginAfterBuildRedirect($params);
                });
                Hooks::Register('formgooglelogin_after_build_redirect', function (&$params = null) {
                    return $this->getFormGoogleLoginAfterBuildRedirect($params);
                });
                Hooks::Register('profilehomepage_after_render', function (&$params = null) {
                    return $this->getProfileHomepageAfterRender($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('navigationservice_construct', function (&$params = null) {
                    return $this->getNavigationServiceConstruct($params);
                });
                Hooks::Register('pagetype_construct', function (&$params = null) {
                    return $this->getPageTypeConstruct($params);
                });
                Hooks::Register('widget_construct', function (&$params = null) {
                    return $this->getWidgetConstruct($params);
                });
                Hooks::Register('pagewidget_construct', function (&$params = null) {
                    return $this->getPageWidgetConstruct($params);
                });
                Hooks::Register('searchengine_before_return_elastictype', function (&$params = null) {
                    return $this->getSearchEngineBeforeReturnElasticType($params);
                });
                Hooks::Register('searchengine_before_return_modulealias', function (&$params = null) {
                    return $this->getSearchEngineBeforeReturnModuleAlias($params);
                });
                Hooks::Register('systemfunct_after_build_constraint', function (&$params = null) {
                    return $this->getSystemFunctAfterBuildConstraint($params);
                });
                Hooks::Register('dbfunct_after_load_domaintablename', function (&$params = null) {
                    return $this->getDbFunctAfterLoadDomainTableName($params);
                });
                Hooks::Register('dbfunct_before_load_domainsingleobject', function (&$params = null) {
                    return $this->getDbFunctBeforeLoadDomainSingleObject($params);
                });
                Hooks::Register('dbfunct_before_load_domainmultipleobject', function (&$params = null) {
                    return $this->getDbFunctBeforeLoadDomainMultipleObject($params);
                });
                Hooks::Register('dbfunct_before_load_mainmultipleobject', function (&$params = null) {
                    return $this->getDbFunctBeforeLoadMainMultipleObject($params);
                });
                Hooks::Register('constants_after_load_themenaviagation', function (&$params = null) {
                    return $this->getConstantsBeforeLoadDefinesAfterLoadThemeNaviagation($params);
                });
                Hooks::Register('constants_before_load_defines', function (&$params = null) {
                    return $this->getConstantsBeforeLoadDefines($params);
                });
                Hooks::Register('pagewidgetservice_after_add_pagedefaultwidgets', function (&$params = null) {
                    return $this->getWysiwygAfterAddPageDefaultWidgets($params);
                });
                Hooks::Register('addnewwidget_after_add_widgettype', function (&$params = null) {
                    return $this->getAddNewWidgetAfterAddWidgetType($params);
                });
                Hooks::Register('modulefilter_before_setup_filterview', function (&$params = null) {
                    return $this->getModuleFilterBeforeSetupFilterView($params);
                });
                Hooks::Register('synchronizacommand_after_setup_availablemodules', function (&$params = null) {
                    return $this->getSynchronizCommandAfterSetupAvailableModules($params);
                });
                Hooks::Register('synchronizacommand_after_modules_synchronize', function (&$params = null) {
                    return $this->getSynchronizaCommandAfterModulesSynchronize($params);
                });
                Hooks::Register('synchronizacommand_after_categories_synchronize', function (&$params = null) {
                    return $this->getSynchronizaCommandAfterCategoriesSynchronize($params);
                });
                Hooks::Register('wysiwygextension_before_validate_widget', function (&$params = null) {
                    return $this->getWysiwygExtensionBeforeValidateWidget($params);
                });
                Hooks::Register('summaryextension_before_render_summary', function (&$params = null) {
                    return $this->getSummaryExtensionBeforeRenderSummary($params);
                });
                Hooks::Register('modules_before_return_availablemodules', function (&$params = null) {
                    return $this->getModulesBeforeReturnAvailableModules($params);
                });
                Hooks::Register('modules_before_return_ismoduleavailable', function (&$params = null) {
                    return $this->getModulesBeforeReturnIsModuleavAilable($params);
                });
                Hooks::Register('modules_before_return_ismodule', function (&$params = null) {
                    return $this->getModulesBeforeReturnIsModule($params);
                });
                Hooks::Register('logincode_before_loginredirect', function (&$params = null) {
                    return $this->getLoginCodeBeforeLoginRedirect($params);
                });
                Hooks::Register('accountcode_before_redirect', function (&$params = null) {
                    return $this->getAccountCodeBeforeRedirect($params);
                });
                Hooks::Register('emailnotificationservice_construct', function (&$params = null) {
                    return $this->getEmailNotificationServiceConstruct($params);
                });
                Hooks::Register('categoryhelper_before_return_categoryrepository', function (&$params = null) {
                    return $this->getCategoryHelperBeforeReturnCategoryRepository($params);
                });
                Hooks::Register('search-fields-block_hide_search-location', function (&$params = null) {
                    return $this->getSearchFieldsBlockHideSearchLocation($params);
                });
                Hooks::Register('blocks-extension_before_return_rendered-card-type-block', function (&$params = null) {
                    return $this->getBlocksExtensionBeforeReturnRenderedCardTypeBlock($params);
                });
            }
            Hooks::Register('legacy_categoryactionajaxcode_ovewrite_removeimagetype', function (&$params = null) {
                $this->getLegacyCategoryActionAjaxCodeOverwriteRemoveImageType($params);
            });
            Hooks::Register('legacy_categoryactionajaxcode_ovewrite_removeicontype', function (&$params = null) {
                $this->getLegacyCategoryActionAjaxCodeOverwriteRemoveIconType($params);
            });
            Hooks::Register('legacy_categoryactionajaxcode_ovewrite_deleteaction', function (&$params = null) {
                $this->getLegacyCategoryActionAjaxCodeOverwriteDeleteAction($params);
            });
            Hooks::Register('legacy_frontend_footer_avoid_categories_js_include', function (&$params = null) {
                return $this->getLegacyFrontendFooterAvoidCategoriesJsInclude($params);
            });
            Hooks::Register('legacy_sitemgr_customjs_modules_avoid_categories_js_include', function (&$params = null) {
                return $this->getLegacySitemgrCustomjsModulesAvoidCategoriesJsInclude($params);
            });
            Hooks::Register('validatefunct_before_categories_friendly_url_check', function (&$params = null) {
                return $this->getValidateFunctBeforeCategoriesFriendlyUrlCheck($params);
            });
            Hooks::Register('searchblock_construct', function (&$params = null) {
                return $this->getSearchBlockConstruct($params);
            });

            // Todo: revise hooks names
            Hooks::Register('forum_question_form', function (&$params = null) {
                return $this->getQuestionForm($params);
            });
            Hooks::Register('forum_question_form_profile', function (&$params = null) {
                return $this->getForumQuestionToProfile($params);
            });
            Hooks::Register('forum_answer_form_to_profile', function (&$params = null) {
                return $this->getForumAnswerFormToProfile($params);
            });
            Hooks::Register('forum_modal_delete', function (&$params = null) {
                return $this->getForumModalDelete($params);
            });
            Hooks::Register('forum_question_manager', function (&$params = null) {
                return $this->getQuestionManager($params);
            });
            Hooks::Register('legacy-sitemgr-content-forum-report_after_check-permissions', function (&$params = null) {
                $this->getLegacySitemgrContentForumReportAfterCheckPermissions($params);
            });
            Hooks::Register('legacy-sitemgr-content-forum-report_after_check-registration', function (&$params = null) {
                $this->getLegacySitemgrContentForumReportAfterCheckRegistration($params);
            });
            Hooks::Register('forum_answer_manager', function (&$params = null) {
                return $this->getAnswerManager($params);
            });
            Hooks::Register('forum_answer_form', function (&$params = null) {
                return $this->getAnswerForm($params);
            });
            Hooks::Register('forum_validation', function (&$params = null) {
            });
            Hooks::Register('loadpage_after_add_pages', function (&$params = null) {
                return $this->getLoadPageAfterAddPages($params);
            });
            Hooks::Register('loadpagetype_after_add_pagetypes', function (&$params = null) {
                return $this->getLoadPageTypeAfterAddPageTypes($params);
            });
            Hooks::Register('loadwidget_after_add_standardwidget', function (&$params = null) {
                return $this->getLoadWidgetAfterAddStandardWidget($params);
            });
            Hooks::Register('themeservice_after_add_commonwidgets', function (&$params = null) {
                return $this->getThemeServiceAfterAddCommonWidgets($params);
            });


            Hooks::Register('enus_before_load_defines', function (&$params = null) {
                return $this->getLanguageBeforeLoadDefines($params, 'en');
            });
            Hooks::Register('gege_before_load_defines', function (&$params = null) {
                return $this->getLanguageBeforeLoadDefines($params, 'de');
            });
            Hooks::Register('trtr_before_load_defines', function (&$params = null) {
                return $this->getLanguageBeforeLoadDefines($params, 'tr');
            });
            Hooks::Register('eses_before_load_defines', function (&$params = null) {
                return $this->getLanguageBeforeLoadDefines($params, 'es');
            });
            Hooks::Register('itit_before_load_defines', function (&$params = null) {
                return $this->getLanguageBeforeLoadDefines($params, 'it');
            });
            Hooks::Register('ptbr_before_load_defines', function (&$params = null) {
                return $this->getLanguageBeforeLoadDefines($params, 'pt');
            });
            Hooks::Register('frfr_before_load_defines', function (&$params = null) {
                return $this->getLanguageBeforeLoadDefines($params, 'fr');
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of CommunityForumBundle.php', ['exception' => $e]);
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
     * @param null $params
     * @throws Exception
     */
    private function getLegacyFrontendFooterAvoidCategoriesJsInclude(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $currentPage = $params['current_page'];
                if(empty($currentPage) || (string_strpos($currentPage, 'forum/answer.php') === false && string_strpos($currentPage, 'forum/question.php') === false)) {
                    $params['_return'] = !empty($params['_return']);
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyFrontendFooterAvoidCategoriesJsInclude method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyCategoryActionAjaxCodeOverwriteDeleteAction(&$params = null)
    {
        $hookReturnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $responseErrorArray = $params['response_error_array_model'];
                $responseSuccessArray = $params['response_success_array_model'];
                $responseStringValueRef = &$params['response_string_value'];
                $httpPostArrayRef = &$params['http_post_array'];
                $httpGetArrayRef = &$params['http_get_array'];
                if(!empty($httpPostArrayRef) && !empty($_POST['module']) && !empty($responseErrorArray) && !empty($responseSuccessArray)) {
                    $moduleFromPostRef = &$_POST['module'];
                    $hookReturnValue = true;
                    try {
                        if(strtolower($moduleFromPostRef)!=='question'){
                            $hookReturnValue = false;
                        } else {
                            if(!empty($_POST['id']) && is_numeric($_POST['id'])) {
                                $parentCategoryIdRef = &$_POST['id'];
                                /** @var QuestionCategoryService $questionCategoryService */
                                $questionCategoryService = $this->container->get('question.category.service');
                                if ($questionCategoryService !== null) {
                                    if(!$questionCategoryService->isAnyEntityAssociatedWithCategoryTreeById($parentCategoryIdRef)) {
                                        $questionCategoryService->deleteCategory($parentCategoryIdRef);
                                        $responseStringValueRef = json_encode($responseSuccessArray);
                                    } else {
                                        $exceptionMessage = 'Category cannot be deleted because itself or any of its subcategories are still being used by a Topic.';
                                        /** @var TranslatorInterface $translator */
                                        $translator = $this->container->get('translator');
                                        if ($translator !== null) {
                                            $locale = $this->getCurrentISOLang();
                                            $exceptionMessage = $translator->trans('Category cannot be deleted because itself or any of its subcategories are still being used by a Topic.', array(), 'messages', $locale);
                                        }

                                        $responseErrorArray = array(
                                            'exception' => false,
                                            'exceptionMessage' => 'No message',
                                            'exceptionStackTrace' => 'No stacktrace',
                                            'causeJsError' => false,
                                            'frontMessage' => $exceptionMessage
                                        );

                                        $responseStringValueRef = json_encode($responseErrorArray);
                                    }
                                }
                                unset($questionCategoryService);
                            }
                        }
                    } catch (Exception $innerException) {
                        $responseErrorArray['exceptionMessage'] = $innerException->getMessage();
                        $responseErrorArray['exceptionStackTrace'] = $innerException->getTraceAsString();
                        $responseStringValueRef = json_encode($responseErrorArray);
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyCategoryActionAjaxCodeOverwriteDeleteAction method of CommunityForumBundle.php', ['exception' => $e]);
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
        $params['_return'] = !empty($params['_return']) || $hookReturnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyCategoryActionAjaxCodeOverwriteRemoveImageType(&$params = null)
    {
        $hookReturnValue = false;
        if (!empty($params) && !empty($this->container) && !empty($params['response_array']) && array_key_exists('response_status', $params)) {
            $responseArrayRef = &$params['response_array'];
            $responseStatusRef = &$params['response_status'];
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $imageIdRef = &$params['image_id'];
                $httpPostArrayRef = &$params['http_post_array'];
                $httpGetArrayRef = &$params['http_get_array'];
                if(!empty($httpPostArrayRef) && !empty($_POST['module']) && !empty($responseArrayRef)) {
                    $moduleFromPostRef = &$_POST['module'];
                    $hookReturnValue = true;
                    try {
                        if(strtolower($moduleFromPostRef)!=='question'){
                            $hookReturnValue = false;
                        } else {
                            if(!empty($_POST['id']) && is_numeric($_POST['id'])) {
                                /** @var QuestionCategoryService $questionCategoryService */
                                $questionCategoryService = $this->container->get('question.category.service');
                                if ($questionCategoryService !== null) {
                                    /** @var DoctrineRegistry $doctrine */
                                    $doctrine = $this->container->get('doctrine');
                                    if ($doctrine !== null) {
                                        /** @var QuestionCategoryRepository $questionCategoryRepository */
                                        $questionCategoryRepository = $doctrine->getRepository('CommunityForumBundle:QuestionCategory');
                                        if ($questionCategoryRepository !== null) {
                                            /** @var QuestionCategory $questionCategory */
                                            $questionCategory = $questionCategoryRepository->find($_POST['id']);
                                            $categoryParent = $questionCategory->getParent();
                                            $categoryParentId = ($categoryParent !== null) ? $categoryParent->getId() : null;
                                            unset($categoryParent);
                                            $formData = array(
                                                'category_id' => $questionCategory->getId(),
                                                'title' => $questionCategory->getTitle(),
                                                'parent_id' => $categoryParentId,
                                                'content' => $questionCategory->getContent(),
                                                'friendly_url' => $questionCategory->getFriendlyUrl(),
                                                'icon_id' => $questionCategory->getIconId(),
                                                'image_id' => null,
                                                'page_title' => $questionCategory->getPageTitle(),
                                                'keywords' => $questionCategory->getKeyWords(),
                                                'seo_keywords' => $questionCategory->getSeoKeywords(),
                                                'seo_description' => $questionCategory->getSeoDescription(),
                                                'clickToDisable' => strtolower($questionCategory->getEnabled()) === 'y',
                                                'featured' => strtolower($questionCategory->getFeatured()) === 'y'
                                            );
                                            $questionCategoryService->saveCategory($formData);
                                        }
                                        unset($questionCategoryRepository);
                                        $responseStatusRef = true;
                                    }
                                    unset($doctrine);
                                }
                                unset($questionCategoryService);
                            }
                        }
                    } catch (Exception $innerException) {
                        $responseStatusRef = false;
                        $responseArrayRef['exception'] = true;
                        $responseArrayRef['exceptionMessage'] = $innerException->getMessage();
                        $responseArrayRef['exceptionStackTrace'] = $innerException->getTraceAsString();
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyCategoryActionAjaxCodeOverwriteRemoveImageType method of CommunityForumBundle.php', ['exception' => $e]);
                    $responseStatusRef = false;
                    $responseArrayRef['exception'] = true;
                    $responseArrayRef['exceptionMessage'] = $e->getMessage();
                    $responseArrayRef['exceptionStackTrace'] = $e->getTraceAsString();
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
        $params['_return'] = !empty($params['_return']) || $hookReturnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyCategoryActionAjaxCodeOverwriteRemoveIconType(&$params = null): void
    {
        $hookReturnValue = false;
        if (!empty($params) && !empty($this->container) && !empty($params['response_array']) && array_key_exists('response_status', $params)) {
            $responseArrayRef = &$params['response_array'];
            $responseStatusRef = &$params['response_status'];
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $iconIdRef = &$params['icon_id'];
                $httpPostArrayRef = &$params['http_post_array'];
                $httpGetArrayRef = &$params['http_get_array'];
                if(!empty($httpPostArrayRef) && !empty($_POST['module']) && !empty($responseArrayRef)) {
                    $moduleFromPostRef = &$_POST['module'];
                    $hookReturnValue = true;
                    try {
                        if(strtolower($moduleFromPostRef)!=='question'){
                            $hookReturnValue = false;
                        } else {
                            if(!empty($_POST['id']) && is_numeric($_POST['id'])) {
                                /** @var QuestionCategoryService $questionCategoryService */
                                $questionCategoryService = $this->container->get('question.category.service');
                                if ($questionCategoryService !== null) {
                                    /** @var DoctrineRegistry $doctrine */
                                    $doctrine = $this->container->get('doctrine');
                                    if ($doctrine !== null) {
                                        /** @var QuestionCategoryRepository $questionCategoryRepository */
                                        $questionCategoryRepository = $doctrine->getRepository('CommunityForumBundle:QuestionCategory');
                                        if ($questionCategoryRepository !== null) {
                                            /** @var QuestionCategory $questionCategory */
                                            $questionCategory = $questionCategoryRepository->find($_POST['id']);
                                            $categoryParent = $questionCategory->getParent();
                                            $categoryParentId = ($categoryParent !== null) ? $categoryParent->getId() : null;
                                            unset($categoryParent);
                                            $formData = array(
                                                'category_id' => $questionCategory->getId(),
                                                'title' => $questionCategory->getTitle(),
                                                'parent_id' => $categoryParentId,
                                                'content' => $questionCategory->getContent(),
                                                'friendly_url' => $questionCategory->getFriendlyUrl(),
                                                'icon_id' => null,
                                                'image_id' => $questionCategory->getImageId(),
                                                'page_title' => $questionCategory->getPageTitle(),
                                                'keywords' => $questionCategory->getKeyWords(),
                                                'seo_keywords' => $questionCategory->getSeoKeywords(),
                                                'seo_description' => $questionCategory->getSeoDescription(),
                                                'clickToDisable' => strtolower($questionCategory->getEnabled()) === 'y',
                                                'featured' => strtolower($questionCategory->getFeatured()) === 'y'
                                            );
                                            $questionCategoryService->saveCategory($formData);
                                        }
                                        unset($questionCategoryRepository);
                                        $responseStatusRef = true;
                                    }
                                    unset($doctrine);
                                }
                                unset($questionCategoryService);
                            }
                        }
                    } catch (Exception $innerException) {
                        $responseStatusRef = false;
                        $responseArrayRef['exception'] = true;
                        $responseArrayRef['exceptionMessage'] = $innerException->getMessage();
                        $responseArrayRef['exceptionStackTrace'] = $innerException->getTraceAsString();
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyCategoryActionAjaxCodeOverwriteRemoveImageType method of CommunityForumBundle.php', ['exception' => $e]);
                    $responseStatusRef = false;
                    $responseArrayRef['exception'] = true;
                    $responseArrayRef['exceptionMessage'] = $e->getMessage();
                    $responseArrayRef['exceptionStackTrace'] = $e->getTraceAsString();
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
        $params['_return'] = !empty($params['_return']) || $hookReturnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacySitemgrCustomjsModulesAvoidCategoriesJsInclude(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $currentPage = $params['current_page'];
                if(empty($currentPage) || (string_strpos($currentPage, 'forum/answer.php') === false && string_strpos($currentPage, 'forum/question.php') === false)) {
                    $params['_return'] = !empty($params['_return']);
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacySitemgrCustomjsModulesAvoidCategoriesJsInclude method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyAddMultCategoriesCodeBeforeSetCategoryObjectValues(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $categoryModuleObjRef = &$params['category_module_obj'];
                $httpPostArray = $params['http_post_array'];
                $httpGetArray = $params['http_get_array'];
                if(!empty($httpPostArray) && array_key_exists('moduleFolder',$httpPostArray) &&  $httpPostArray['moduleFolder']==='forum') {
                    $categoryModuleObjRef = new LegacyQuestionCategory();
                } else {
                    $params['_return'] = !empty($params['_return']);
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyAddMultCategoriesCodeBeforeSetCategoryObjectValues method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyAddMultCategoriesCodeAfterSetCategoryModuleTable(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $categoryModuleTableRef = &$params['category_module_table'];
                $httpPostArray = $params['http_post_array'];
                $httpGetArray = $params['http_get_array'];
                if(!empty($httpPostArray) && array_key_exists('moduleFolder',$httpPostArray) &&  $httpPostArray['moduleFolder']=='forum') {
                    $categoryModuleTableRef = 'QuestionCategory';
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyAddMultCategoriesCodeAfterSetCategoryModuleTable method of CommunityForumBundle.php', ['exception' => $e]);
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
    }


    /**
     * @param null $params
     * @throws Exception
     */
    private function getBlocksExtensionBeforeReturnRenderedCardTypeBlock(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $module = &$params['module'];
                if(!empty($module) && ($module=='question' || $module=='forum')) {
                    $twigName = &$params['twigName'];
                    $cardType = &$params['cardType'];
                    if(!empty($cardType)) {
                        $items = &$params['items'];
                        $itemsPerRow = &$params['itemsPerRow'];
                        $banner = &$params['banner'];
                        $content = &$params['content'];
                        $widgetLink = &$params['widgetLink'];
                        $module = &$params['module'];
                        $twigName = "CommunityForumBundle::blocks/cards/$cardType.html.twig";
                    } else {
                        throw new Exception("Empty card type before render a card.");
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getBlocksExtensionBeforeReturnRenderedCardTypeBlock method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getSearchFieldsBlockHideSearchLocation(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $module = $params['module'];
                $returnValue = !empty($module) && $module === 'forum';

                $params['_return'] = !empty($params['_return']) || $returnValue;
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getSearchFieldsBlockHideSearchLocation method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getSearchBlockConstruct(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $that = &$params['that'];
                if(!empty($that)) {
                    if (!array_key_exists('question', SearchBlock::$previousItems)) {
                        SearchBlock::$previousItems['question'] = [];
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getSearchBlockConstruct method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    /**
     * @param null $params
     * @param $localeAcronimn
     * @throws Exception
     */
    private function getLanguageBeforeLoadDefines(&$params = null, $localeAcronimn)
    {
        if (empty($localeAcronimn) || ! in_array($localeAcronimn, ['en','de','es','tr','it','pt','fr']) ){
            $localeAcronimn = 'en';
        }
        if (!empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $translator = $this->container->get('translator');
                if (!empty($translator)) {
                    define("LANG_LABEL_EDIT_QUESTION", $translator->trans('Forum topic edit',[],'messages', $localeAcronimn));
                    define("LANG_LABEL_EDIT_ANSWER", $translator->trans('Forum answer edit',[],'messages', $localeAcronimn));
                }
                unset($translator);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLanguageBeforeLoadDefines method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getValidateFunctBeforeCategoriesFriendlyUrlCheck(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $validateModulesRef = &$params['validate_modules'];
                if(isset($validateModulesRef) && is_array($validateModulesRef)) {
                    if(empty($validateModulesRef) || ( array_search('QuestionCategory',$validateModulesRef) === false )){
                        $validateModulesRef[] = 'QuestionCategory';
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getValidateFunctBeforeCategoriesFriendlyUrlCheck method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    private function getBulkUpdateAfterLoadModuleName(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'forum/answers')) {
            $params['typeName'] = 'Answer';
        } elseif (string_strpos($_SERVER['PHP_SELF'], 'forum')) {
            $params['typeName'] = 'Question';
        }
    }

    private function getBulkUpdateAfterLoadRedirectRoute(&$params = null)
    {
        if ($params['typeName'] == 'Question') {
            $params['header_location'] = '/content/forum';
        } elseif ($params['typeName'] == 'Answer') {
            $params['header_location'] = '/content/forum/answers';
        }
    }

    private function getFormBulkBeforeLoadCategoryTree(&$params = null)
    {
        if ($params['bulkType'] == 'answer') {
            $params['orderby'] = '`description`';
            $params['hideCategTree'] = true;
        }
    }

    private function getFormBulkAfterLoadCategoryTree(&$params = null)
    {
        $translator = $this->container->get('translator');

        if ($params['manageModule'] == 'question') {
            $params['categoryDropDown'] = html_selectBox_BulkUpdate('add_category_id', $params['nameArray'],
                $params['valueArray'],
                '', '', '', $translator->trans('Change Category'), $params['valueCatArray']);
        }
    }

    private function getFormBulkBeforeRenderStatus(&$params = null)
    {
        if ($params['bulkType'] == 'question' || $params['bulkType'] == 'answer') {
            $params['allowStatus'] = false;
        }
    }

    private function getFormBulkBeforeRenderRenewalDate(&$params = null)
    {
        if ($params['bulkType'] == 'question' || $params['bulkType'] == 'answer') {
            $params['allowRenewalDate'] = false;
        }
    }

    private function getFormBulkBeforeRenderRenewalLevel(&$params = null)
    {
        if ($params['bulkType'] == 'question' || $params['bulkType'] == 'answer') {
            $params['allowLevel'] = false;
        }
    }

    private function getBulkUpdateBeforeLoadDeleteObject(&$params = null)
    {
        if ($params['typeName'] == 'Question') {
            $params['auxtypeName'] = Question::class;
        } else if ($params['typeName'] == 'Answer') {
            $params['auxtypeName'] = Answer::class;
        }
    }

    private function getBulkUpdateBeforeLoadCategoryObject(&$params = null)
    {
        if ($params['typeName'] == 'Question') {
            $params['auxtypeName'] = Question::class;
        } else if ($params['typeName'] == 'Answer') {
            $params['auxtypeName'] = Answer::class;
        }
    }

    private function getBulkUpdateBeforeLoadUpdateObject(&$params = null)
    {
        if ($params['typeName'] == 'Question') {
            $params['auxtypeName'] = Question::class;
        } else if ($params['typeName'] == 'Answer') {
            $params['auxtypeName'] = Answer::class;
        }
    }

    private function getSubmenuContentAfterLoadModules(&$params = null)
    {
        $translator = $this->container->get('translator');

        if (string_strpos($_SERVER['PHP_SELF'], FORUM_FEATURE_NAME.'/index.php') !== false) {
            $params['linkClaim'] = false;
            $params['manageSearch'] = true;
            $params['moduleFolder'] = FORUM_FEATURE_NAME;
            $params['labelManage'] = $translator->trans('Topics');
        } elseif (string_strpos($_SERVER['PHP_SELF'], FORUM_FEATURE_NAME.'/answers/index.php') !== false) {
            $params['linkClaim'] = false;
            $params['manageSearch'] = true;
            $params['moduleFolder'] = FORUM_FEATURE_NAME;
            $params['labelManage'] = $translator->trans('Topics');
        } elseif (string_strpos($_SERVER['PHP_SELF'], FORUM_FEATURE_NAME.'/categories/index.php') !== false) {
            $params['linkAddItem'] = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/'.FORUM_FEATURE_NAME.'/categories/category.php'.($_GET['category_id'] ? '?category_id='.$_GET['category_id'] : '');
            $params['labelAddItem'] = $translator->trans('Add Category');
            $params['linkClaim'] = false;
            $params['manageSearch'] = false;
            $params['categoryItem'] = true;
            $params['moduleFolder'] = FORUM_FEATURE_NAME;
            $params['labelAddMultItem'] = LANG_SITEMGR_ADD_MULT_CATEGORY;
            $params['labelManage'] = $translator->trans('Topics');
        }
    }

    private function getAdminModuleBeforeCategorySelectQuery(&$params = null)
    {
        if (strtolower($params['manageModule']) == 'question') {
            $params['sql'] = "SELECT id FROM Question WHERE category_id = {$params['search_category_id']}";
        } elseif (strtolower($params['manageModule']) == 'answer') {
            $params['sql'] = "SELECT id FROM Answer WHERE category_id = {$params['search_category_id']}";
        }
    }

    private function getAdminModuleAfterBuildTitleQuery(&$params = null)
    {
        if (strtolower($params['manageModule']) == 'question') {
            array_pop($params['sql_where']);
            array_pop($params['search_for_keyword_fields']);

            $params['sql_where'][] = ' title LIKE '.db_formatString('%'.$params['search_title'].'%').' ';
            $params['search_title'] = false;
        } elseif ($params['manageModule'] == 'answer') {
            array_pop($params['sql_where']);
            array_pop($params['search_for_keyword_fields']);

            $params['sql_where'][] = ' description LIKE '.db_formatString('%'.$params['search_title'].'%').' ';
            $params['search_title'] = false;
        }
    }

    private function getAdminModuleAfterBuildAccountQuery(&$params = null)
    {
        if ($params['manageModule'] == 'question' || $params['manageModule'] == 'answer') {
            if ($params['search_account_id'] && is_numeric($params['search_account_id'])) {
                array_pop($params['sql_where']);
                $params['sql_where'][] = " account_id = {$params['search_account_id']} ";
            }
        }
    }

    private function getAdminModuleBeforeLoadDeleteObject(&$params = null)
    {
        $moduleObj = strtolower($params['objStr']);
        if ($moduleObj == 'question') {
            $params['objStr'] = Question::class;
        } elseif ($moduleObj == 'answer') {
            $params['objStr'] = Answer::class;
        }
    }

    private function getColorSchemeFunctBeforeWriteCustomCss(&$params = null)
    {
        $params['phpContent'] .= '
            .question-horizontal-bar {
                background-color: #'.$params['colors']['color1'].' !important;
            }
            .question.sidebar .badge.badge-success {
                background-color: #'.$params['colors']['color1'].';
            }';
    }

    private function getListModuleBeforeLoadItemData(&$params = null)
    {

    }

    private function getListModuleAfterLoadItemData(&$params = null)
    {
        if ($params['manageModule'] == 'question' || $params['manageModule'] == 'answer') {
            $params['previewModule'][$params['cont']]['account_id'] = $params['itemList']->getNumber('account_id');
            $params['previewModule'][$params['cont']]['description'] = $params['itemList']->getString('description');
        }

        if ($params['manageModule'] == 'answer' && $questionId = $params['itemList']->getNumber('question_id')) {

            $question = $this->container->get('doctrine')->getRepository('CommunityForumBundle:Question')->find($questionId);

            $params['previewModule'][$params['cont']]['title'] = $question->getTitle();
            $params['previewModule'][$params['cont']]['preview_url'] = $this->container->get('router')->generate('forum_detail',
                [
                    'friendlyUrl' => $question->getFriendlyUrl(),
                    '_format'     => 'html',
                ], UrlGeneratorInterface::ABSOLUTE_PATH);

            $title = html_entity_decode(substr(strip_tags($params['itemList']->getNumber('description')), 0, 100));
            !empty($title) and strlen($title) === 100 and $title .= '...';
            $params['itemList']->setString('title', $title);
        }
    }

    private function getListModuleBeforeLoadPaymentInfo(&$params = null)
    {
        if ($params['manageModule'] == 'question' || $params['manageModule'] == 'answer') {
            $params['allowPaymentInfo'] = false;
        }
    }

    private function getListModuleBeforeLoadRenewalDate(&$params = null)
    {
        if ($params['manageModule'] == 'question' || $params['manageModule'] == 'answer') {
            $params['allowRenewalDate'] = false;
        }
    }

    private function getModalSearchModuleBeforeRenderLevel(&$params = null)
    {
        if ($params['manageModule'] == 'question' || $params['manageModule'] == 'answer') {
            $params['allowLevel'] = false;
        }
    }

    private function getModalSearchModuleBeforeRenderCategory(&$params = null)
    {
        if ($params['manageModule'] == 'answer') {
            $params['allowCategory'] = false;
        }
    }

    private function getModalSearchModuleBeforeRenderExpirationDate(&$params = null)
    {
        if ($params['manageModule'] == 'question' || $params['manageModule'] == 'answer') {
            $params['allowExpirationDate'] = false;
        }
    }

    private function getSubMenuContentAfterRenderModuleButton(&$params = null)
    {
        $translator = $this->container->get('translator');

        if ($params['moduleFolder'] == 'forum') {
            echo '<a href="'.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/'.FORUM_FEATURE_NAME.'/answers/" class="action-button '.(string_strpos($_SERVER['PHP_SELF'],
                    FORUM_FEATURE_NAME.'/answers/index.php') !== false ? 'is-active' : '').'">'.$translator->trans('Answers').'</a>';
        }
    }

    private function getViewModuleBeforeRenderSettingButton(&$params = null)
    {
        if ($params['manageModule'] == 'question' || $params['manageModule'] == 'answer') {
            $params['allowSettingButton'] = false;
        }
    }

    private function getViewModuleAfterRenderDescription(&$params = null)
    {
        $translator = $this->container->get('translator');

        if ($params['manageModule'] == 'question') {
            echo '<h5> '.$translator->trans('Topic').'</h5>
                <div>
                    <p>'.html_entity_decode(substr(strip_tags($params['prevModule']['description']), 0, 255)).'</p>
                </div>';
        }

        if ($params['manageModule'] == 'answer') {
            echo '<h5> '.$translator->trans('Answer').'</h5>
                <div>
                    <p>'.html_entity_decode(substr(strip_tags($params['prevModule']['description']), 0, 255)).'</p>
                </div>';
        }
    }

    private function getModulesFooterAfterRenderJs(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], '/forum/question.php') !== false) {
            echo '<script type="text/javascript">
                function JS_submit() {
                    document.question.submit();
                }
            </script>';
        }

        if (string_strpos($_SERVER['PHP_SELF'], '/forum/answers/answer.php') !== false ||
            string_strpos($_SERVER['PHP_SELF'], '/forum/answer.php') !== false) {
            echo '<script type="text/javascript">
                function JS_submit() {
                    document.answer.submit();
                }
            </script>';
        }
    }

    private function getNavigationServiceConstruct(&$params = null)
    {
        $params['mainHeaderNavigation']['Forum Homepage'] = ['module' => null, 'label' => 'Forum'];
    }

    private function getPageTypeConstruct(&$params = null)
    {

        $params['urlNonEditable'][] = 'Forum Detail';
        $params['pageViewNotAllowed'][] = 'Forum Detail';
        $params['pagesWithoutSEO'][] = 'Forum Detail';
        $params['urlToRoute']['Forum Homepage'] = 'alias_forum_module';
    }

    private function getWidgetConstruct(&$params = null)
    {
        $params['widgetNonDuplicate']['detail'][] = 'Forum Detail';
        $params['widgetNonDuplicate']['forum_questionbar'][] = 'Horizontal Question Bar';
        $params['widgetNonDuplicate']['forum_twocolumns_recentquestions'][] = 'Two columns recent questions';
    }

    private function getPageWidgetConstruct(&$params = null)
    {
        $sitemgrLanguage = 'en';
        if (!empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $translator = $this->container->get('translator');
                if (!empty($translator)) {

                    $that = $params['that'];

                    $params['that']->getForumHomepageDefaultWidgets = function () use (&$that, &$translator, &$sitemgrLanguage) {
                        $pageWidgetsTheme = [
                            Theme::DEFAULT_THEME => [
                                '1' => [
                                    Widget::HEADER => [
                                        'content' => [
                                            'labelDashboard' => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                                            'labelProfile' => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                                            'labelFaq' => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                                            'labelLogOff' => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                                            'labelListWithUs' => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                                            'labelSignIn' => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                                            'labelMore' => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign' => 'true',
                                            'isTransparent' => 'false',
                                            'stickyMenu' => 'false',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '2' => 'Horizontal Question Bar',
                                '3' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'white',
                                        ]
                                    ]
                                ],
                                '4' => [
                                    'Two columns recent questions' => [
                                        'content'  => [
                                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                                            'labelPopularQuestions'   => $translator->trans('Popular topics', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign'           => 'false',
                                            'backgroundColor'     => 'base',
                                        ],
                                    ]
                                ],
                                '5' => [
                                    Widget::LIST_OF_HORIZONTAL_CARDS => [
                                        'content' => [
                                            'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                                            'widgetTitle' => '', //Ex: "Featured Listing"
                                            'widgetLink' => [
                                                'label' => '', //Ex: "view more"
                                                'page_id' => '',
                                                'link' => '', //Ex: "/listing"
                                            ],
                                            'module' => 'question', //listing, event, classified, article, deal, blog
                                            'banner' => '', //null, square, wide skyscraper
                                            'columns' => 1, //2, 3, 4
                                            'items' => [], //items id
                                            'custom' => [
                                                'level' => [], //10, 30, 50, 70
                                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                                'quantity' => 3, //3, 6, 9, 12 or 4, 8, 12, 16 ou 2, 4, 6, 8
                                                'categories' => [], //categories IDs
                                                'locations' => [ //locations IDs
                                                    'location_1' => '',
                                                    'location_2' => '',
                                                    'location_3' => '',
                                                    'location_4' => '',
                                                    'location_5' => '',
                                                ],
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '6' => [
                                    Widget::SPONSORED_LINKS => [
                                        'content' => [
                                            'bannerType' => 'large-mobile',
                                            'isWide' => 'false',
                                            'banners' => [
                                                1 => 'sponsor-links',
                                                2 => 'sponsor-links',
                                                3 => 'sponsor-links',
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '7' => Widget::NEWSLETTER,
                                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                                '9' => Widget::FOOTER,
                            ],
                            Theme::DOCTOR_THEME => [
                                '1' => [
                                    Widget::HEADER_WITH_CONTACT_PHONE => [
                                        'content' => [
                                            'labelDashboard' => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                                            'labelProfile' => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                                            'labelFaq' => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                                            'labelLogOff' => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                                            'labelListWithUs' => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                                            'labelSignIn' => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                                            'labelMore' => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign' => 'true',
                                            'isTransparent' => 'false',
                                            'stickyMenu' => 'false',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '2' => 'Horizontal Question Bar',
                                '3' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'white',
                                        ]
                                    ]
                                ],
                                '4' => [
                                    'Two columns recent questions' => [
                                        'content'  => [
                                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                                            'labelPopularQuestions'   => $translator->trans('Popular topics', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign'           => 'false',
                                            'backgroundColor'     => 'base',
                                        ],
                                    ]
                                ],
                                '5' => Widget::SPONSORED_LINKS,
                                '6' => Widget::FOOTER_WITH_NEWSLETTER,
                            ],
                            Theme::RESTAURANT_THEME => [
                                '1' => Widget::NAVIGATION_WITH_LEFT_LOGO_PLUS_SOCIAL_MEDIA,
                                '2' => 'Horizontal Question Bar',
                                '3' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'white',
                                        ]
                                    ]
                                ],
                                '4' => [
                                    'Two columns recent questions' => [
                                        'content'  => [
                                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                                            'labelPopularQuestions'   => $translator->trans('Popular topics', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign'           => 'false',
                                            'backgroundColor'     => 'base',
                                        ],
                                    ]
                                ],
                                '5' => [
                                    Widget::LIST_OF_HORIZONTAL_CARDS => [
                                        'content' => [
                                            'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                                            'widgetTitle' => '', //Ex: "Featured Listing"
                                            'widgetLink' => [
                                                'label' => '', //Ex: "view more"
                                                'page_id' => '',
                                                'link' => '', //Ex: "/listing"
                                            ],
                                            'module' => 'question', //listing, event, classified, article, deal, blog
                                            'banner' => '', //null, square, wide skyscraper
                                            'columns' => 1, //2, 3, 4
                                            'items' => [], //items id
                                            'custom' => [
                                                'level' => [], //10, 30, 50, 70
                                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                                'quantity' => 3, //3, 6, 9, 12 or 4, 8, 12, 16 ou 2, 4, 6, 8
                                                'categories' => [], //categories IDs
                                                'locations' => [ //locations IDs
                                                    'location_1' => '',
                                                    'location_2' => '',
                                                    'location_3' => '',
                                                    'location_4' => '',
                                                    'location_5' => '',
                                                ],
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '6' => [
                                    Widget::SPONSORED_LINKS => [
                                        'content' => [
                                            'bannerType' => 'large-mobile',
                                            'isWide' => 'false',
                                            'banners' => [
                                                1 => 'sponsor-links',
                                                2 => 'sponsor-links',
                                                3 => 'sponsor-links',
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '7' => Widget::NEWSLETTER,
                                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                                '9' => Widget::FOOTER_WITH_NEWSLETTER,
                            ],
                            Theme::WEDDING_THEME => [
                                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                                '2' => 'Horizontal Question Bar',
                                '3' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'white',
                                        ]
                                    ]
                                ],
                                '4' => [
                                    'Two columns recent questions' => [
                                        'content'  => [
                                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                                            'labelPopularQuestions'   => $translator->trans('Popular topics', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign'           => 'false',
                                            'backgroundColor'     => 'base',
                                        ],
                                    ]
                                ],
                                '5' => [
                                    Widget::LIST_OF_HORIZONTAL_CARDS => [
                                        'content' => [
                                            'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                                            'widgetTitle' => '', //Ex: "Featured Listing"
                                            'widgetLink' => [
                                                'label' => '', //Ex: "view more"
                                                'page_id' => '',
                                                'link' => '', //Ex: "/listing"
                                            ],
                                            'module' => 'question', //listing, event, classified, article, deal, blog
                                            'banner' => '', //null, square, wide skyscraper
                                            'columns' => 1, //2, 3, 4
                                            'items' => [], //items id
                                            'custom' => [
                                                'level' => [], //10, 30, 50, 70
                                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                                'quantity' => 3, //3, 6, 9, 12 or 4, 8, 12, 16 ou 2, 4, 6, 8
                                                'categories' => [], //categories IDs
                                                'locations' => [ //locations IDs
                                                    'location_1' => '',
                                                    'location_2' => '',
                                                    'location_3' => '',
                                                    'location_4' => '',
                                                    'location_5' => '',
                                                ],
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '6' => [
                                    Widget::SPONSORED_LINKS => [
                                        'content' => [
                                            'bannerType' => 'large-mobile',
                                            'isWide' => 'false',
                                            'banners' => [
                                                1 => 'sponsor-links',
                                                2 => 'sponsor-links',
                                                3 => 'sponsor-links',
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '7' => Widget::NEWSLETTER,
                                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                                '9' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
                            ],
                        ];

                        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
                    };

                    $params['that']->getForumDetailDefaultWidgets = function () use (&$that, &$translator, &$sitemgrLanguage) {
                        $pageWidgetsTheme = [
                            Theme::DEFAULT_THEME => [
                                '1' => [
                                    Widget::HEADER => [
                                        'content' => [
                                            'labelDashboard' => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                                            'labelProfile' => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                                            'labelFaq' => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                                            'labelLogOff' => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                                            'labelListWithUs' => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                                            'labelSignIn' => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                                            'labelMore' => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign' => 'true',
                                            'isTransparent' => 'false',
                                            'stickyMenu' => 'false',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '2' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'brand',
                                        ]
                                    ]
                                ],
                                '3' => 'Forum Detail',
                                '4' => [
                                    Widget::SPONSORED_LINKS => [
                                        'content' => [
                                            'bannerType' => 'large-mobile',
                                            'isWide' => 'false',
                                            'banners' => [
                                                1 => 'sponsor-links',
                                                2 => 'sponsor-links',
                                                3 => 'sponsor-links',
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                                '6' => Widget::FOOTER,
                            ],
                            Theme::DOCTOR_THEME => [
                                '1' => [
                                    Widget::HEADER_WITH_CONTACT_PHONE => [
                                        'content' => [
                                            'labelDashboard' => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                                            'labelProfile' => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                                            'labelFaq' => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                                            'labelLogOff' => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                                            'labelListWithUs' => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                                            'labelSignIn' => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                                            'labelMore' => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign' => 'true',
                                            'isTransparent' => 'false',
                                            'stickyMenu' => 'false',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '2' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'brand',
                                        ]
                                    ]
                                ],
                                '3' => 'Forum Detail',
                                '4' => [
                                    Widget::SPONSORED_LINKS => [
                                        'content' => [
                                            'bannerType' => 'large-mobile',
                                            'isWide' => 'false',
                                            'banners' => [
                                                1 => 'sponsor-links',
                                                2 => 'sponsor-links',
                                                3 => 'sponsor-links',
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '5' => Widget::FOOTER_WITH_NEWSLETTER,
                            ],
                            Theme::RESTAURANT_THEME => [
                                '1' => Widget::NAVIGATION_WITH_LEFT_LOGO_PLUS_SOCIAL_MEDIA,
                                '2' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'brand',
                                        ]
                                    ]
                                ],
                                '3' => 'Forum Detail',
                                '4' => [
                                    Widget::SPONSORED_LINKS => [
                                        'content' => [
                                            'bannerType' => 'large-mobile',
                                            'isWide' => 'false',
                                            'banners' => [
                                                1 => 'sponsor-links',
                                                2 => 'sponsor-links',
                                                3 => 'sponsor-links',
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
                            ],
                            Theme::WEDDING_THEME => [
                                '1' => [
                                    Widget::HEADER => [
                                        'content' => [
                                            'labelDashboard' => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                                            'labelProfile' => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                                            'labelFaq' => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                                            'labelLogOff' => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                                            'labelListWithUs' => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                                            'labelSignIn' => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                                            'labelMore' => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                                            'hasDesign' => 'true',
                                            'isTransparent' => 'false',
                                            'stickyMenu' => 'false',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '2' => [
                                    Widget::SEARCH_BAR => [
                                        'content' => [
                                            'placeholderSearchKeyword'  => [
                                                'value' => $translator->trans('Subjects, terms, answers...', [], 'widgets', $sitemgrLanguage),
                                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                                            ],
                                            'hasDesign'                 => 'true',
                                            'backgroundColor'           => 'brand',
                                        ]
                                    ]
                                ],
                                '3' => 'Forum Detail',
                                '4' => [
                                    Widget::SPONSORED_LINKS => [
                                        'content' => [
                                            'bannerType' => 'large-mobile',
                                            'isWide' => 'false',
                                            'banners' => [
                                                1 => 'sponsor-links',
                                                2 => 'sponsor-links',
                                                3 => 'sponsor-links',
                                            ],
                                            'hasDesign' => 'true',
                                            'backgroundColor' => 'base',
                                        ],
                                    ]
                                ],
                                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
                            ],
                        ];

                        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
                    };

                }
                unset($translator);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getPageWidgetConstruct method of CommunityForumBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $levelFields,
                    $levelOptions,
                    $type);
                if (!empty($notLoggedCriticalException)) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    private function getSystemFunctAfterBuildConstraint(&$params = null)
    {
        $params['buffer'] .= 'define("ALIAS_FORUM_MODULE", "'.($params['values']['alias_forum_module'] ? $params['values']['alias_forum_module'] : 'forum').'");'.PHP_EOL;
        $params['buffer'] .= 'define("ALIAS_FORUM_ALLCATEGORIES_URL_DIVISOR", "'.($params['values']['alias_forum_allcategories_url_divisor'] ? $params['values']['alias_forum_allcategories_url_divisor'] : 'categories').'");'.PHP_EOL;
        $params['buffer'] .= 'define("FORUM_FEATURE_NAME", "forum");'.PHP_EOL;
        $params['buffer'] .= 'define("FORUM_FEATURE_NAME_PLURAL", FORUM_FEATURE_NAME."");'.PHP_EOL;
        $params['buffer'] .= 'define("FORUM_DEFAULT_URL", NON_SECURE_URL."/".ALIAS_FORUM_MODULE);'.PHP_EOL.PHP_EOL;
    }

    private function getDbFunctAfterLoadDomainTableName(&$params = null)
    {
        $table = strtolower($params['table']);
        $table = str_replace("arcasolutions\\modstoresbundle\\plugins\\communityforum\\entity\\internal\\", '', $table);

        switch ($table) {
            case 'question':
                $params['obj'] = 'Question';
                break;
            case 'answer':
                $params['obj'] = 'Answer';
                break;
            case 'questioncategory':
                $params['obj'] = 'QuestionCategory';
                break;
        }
    }

    private function getModalSearchModuleAfterLoadModules(&$params = null)
    {
        switch ($params['manageModule']) {
            case 'question' :
                $params['moduleCategory'] = 'QuestionCategory';
                break;
            case 'answer' :
                $params['moduleCategory'] = 'QuestionCategory';
                break;
        }
    }

    private function getDbFunctBeforeLoadDomainSingleObject(&$params = null)
    {
        switch ($params['obj']) {
            case 'QuestionCategory' :
                $params['obj'] = LegacyQuestionCategory::class;
                break;
        }
    }

    private function getDbFunctBeforeLoadDomainMultipleObject(&$params = null)
    {
        switch ($params['obj']) {
            case 'QuestionCategory' :
                $params['obj'] = LegacyQuestionCategory::class;
                break;
        }
    }

    private function getDbFunctBeforeLoadMainMultipleObject(&$params = null)
    {
        switch ($params['obj']) {
            case 'QuestionCategory' :
                $params['obj'] = LegacyQuestionCategory::class;
                break;
        }
    }

    private function getListCategoryBeforeLoadCategory(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'forum') !== false) {
            $params['table_category'] = LegacyQuestionCategory::class;
        }
    }

    private function getListModuleAfterLoadModules(&$params = null)
    {
        $translator = $this->container->get('translator');

        $moduleUrl = $this->container->get('router')->generate('forum_homepage', [],
            UrlGeneratorInterface::ABSOLUTE_PATH);

        switch ($params['manageModule']) {
            case 'question':
                $params['msgSucessUpdate'] = $translator->trans('Topic successfully updated');
                $params['msgSuccessDelete'] = $translator->trans('Topic successfully deleted!');
                $params['itemsList'] = $this->container->get('modstore.storage.service')->retrieve('questions');
                $params['moduleDefaultURL'] = $moduleUrl;
                $params['summaryfield'] = 'description';
                $params['titleField'] = 'title';
                break;
            case 'answer':
                $params['msgSucessUpdate'] = $translator->trans('Answer successfully updated');
                $params['msgSuccessDelete'] = $translator->trans('Answer successfully deleted!');
                $params['itemsList'] = $this->container->get('modstore.storage.service')->retrieve('answers');
                $params['moduleDefaultURL'] = $moduleUrl;
                $params['summaryfield'] = 'description';
                $params['titleField'] = 'title';
                break;
        }
    }

    private function getListCategoryBeforeRenderSubCategoriesButton(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'forum') != false) {
            $params['subcategories'] = [];
        }
    }

    private function getListCategoryBeforeRenderSubCategoriesCounter(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'forum') != false) {
            $params['maxLevelCat'] = 0;
        }
    }

    private function getListCategoryBeforeRenderAddSubCategoriesButton(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'forum') != false) {
            $params['maxLevelCat'] = 0;
        }
    }

    private function getConstantsBeforeLoadDefinesAfterLoadThemeNaviagation(&$params = null)
    {
        $translator = $this->container->get('translator');

        $params['array_navigation']['header'][] = [
            'name' => $translator->trans('Forum'),
            'url'  => 'FORUM_DEFAULT_URL',
        ];

        $params['array_navigation']['footer'][] = [
            'name' => $translator->trans('Forum'),
            'url'  => 'FORUM_DEFAULT_URL',
        ];
    }

    private function getConstantsBeforeLoadDefines(&$params = null)
    {
        define('ALIAS_FORUM_MODULE', 'forum');
        define('FORUM_FEATURE_NAME', 'forum');
        define('ALIAS_FORUM_ALLCATEGORIES_URL_DIVISOR', 'categories');
        define('FORUM_FEATURE_NAME_PLURAL', FORUM_FEATURE_NAME.'');
        define('FORUM_DEFAULT_URL', NON_SECURE_URL.'/'.ALIAS_FORUM_MODULE);//NON_SECURE_URL does not exists
    }

    private function getWysiwygAfterAddPageDefaultWidgets(&$params = null)
    {
        $params['pagesDefault']['Forum Homepage'] = call_user_func($params['that']->getForumHomepageDefaultWidgets);
        $params['pagesDefault']['Forum Detail'] = call_user_func($params['that']->getForumDetailDefaultWidgets);
    }

    private function getAddNewWidgetAfterAddWidgetType(&$params = null)
    {
        $trans = $this->container->get('translator');

        $params['widgetTypes']['forum'] = $trans->trans('Forum', [], 'widgets', /** @Ignore */
            $params['sitemgrLanguage']);
    }

    private function getSynchronizCommandAfterSetupAvailableModules(&$params = null)
    {
        $params['availableModules']['question'] = 1 << count($params['availableModules']);
        $params['availableModules']['questionCategory'] = 1 << count($params['availableModules']);
    }

    private function getSynchronizaCommandAfterModulesSynchronize(&$params = null)
    {
        $moduleFlags = &$params['moduleFlags'];
        $availableModules = &$params['availableModules'];
        /**
         * @var ImportLog $import
         */
        $import = &$params['import'];
        $output = &$params['output'];
        $input = &$params['input'];

        if(!empty($output) && ($output instanceof OutputInterface) && !empty($input) && ($input instanceof InputInterface) && !empty($availableModules) && array_key_exists('question', $availableModules) && ($import instanceof ImportLog || $import === null)) {
            if (($moduleFlags === 0 or $moduleFlags & $availableModules['question']) && (!$import || $import->getModule() == "question")) {
                $output->writeln("\n<info>========= Question =========</info>");

                $synchronizable = 'question.synchronization';
                $start = microtime(true);

                if($total = $input->getOption('partial')) {
                    if ($input->getOption('module')) {
                        $this->container->get($synchronizable)->generatePartial($output, $total,
                            $input->getOption('bulk-size'));
                        $this->container->get('elasticsearch.synchronization')->synchronize();
                        $output->writeln(sprintf("\n\nOperation took %d seconds", microtime(true) - $start));
                    } else {
                        $output->writeln("\n<error>Module Parameter is required for Partial Synchronization</error>");
                    }
                    return;
                } else {
                    $this->container->get($synchronizable)->generateAll($output, $input->getOption('bulk-size'));
                    $this->container->get('elasticsearch.synchronization')->synchronize();
                    $output->writeln(sprintf("\n\nOperation took %d seconds", microtime(true) - $start));
                }
            }
        }
    }

    private function getSynchronizaCommandAfterCategoriesSynchronize(&$params = null)
    {
        $moduleFlags = $params['moduleFlags'];
        $availableModules = $params['availableModules'];
        /**
         * @var ImportLog $import
         */
        $import = $params['import'];
        $output = $params['output'];
        $input = $params['input'];

        if(!empty($output) && ($output instanceof OutputInterface) && !empty($input) && ($input instanceof InputInterface) && !empty($availableModules) && array_key_exists('questionCategory', $availableModules) && ($import instanceof ImportLog || $import === null)) {
            if (($moduleFlags === 0 or $moduleFlags & $availableModules['questionCategory']) && (!$import || $import->getModule() == "questionCategory")) {
                $output->writeln("\n<info>========= Question Category =========</info>");

                $synchronizable = 'question.category.synchronization';
                $start = microtime(true);

                if($total = $input->getOption('partial')) {
                    if ($input->getOption('module')) {
                        $this->container->get($synchronizable)->generatePartial($output, $total,
                            $input->getOption('bulk-size'));
                        $this->container->get('elasticsearch.synchronization')->synchronize();
                        $output->writeln(sprintf("\n\nOperation took %d seconds", microtime(true) - $start));
                    } else {
                        $output->writeln("\n<error>Module Parameter is required for Partial Synchronization</error>");
                    }
                    return;
                } else {
                    $this->container->get($synchronizable)->generateAll($output, $input->getOption('bulk-size'));
                    $this->container->get('elasticsearch.synchronization')->synchronize();
                    $output->writeln(sprintf("\n\nOperation took %d seconds", microtime(true) - $start));
                }
            }
        }
    }

    private function getModulesBeforeReturnAvailableModules(&$params = null)
    {
        $params['modules']['question'] = true;
    }

    private function getModulesBeforeReturnIsModuleavAilable(&$params = null)
    {
        if ($params['module'] == 'forum' || $params['module'] == 'question') {
            $params['available'] = true;
        }
    }

    private function getModulesBeforeReturnIsModule(&$params = null)
    {
        if ($params['possibleModule'] == 'forum' || $params['possibleModule'] == 'question') {
            $params['isModule'] = true;
        }
    }

    private function getClassPagebrowsingBeforeRetrieveRowObject(&$params = null)
    {
        if ($params['class'] == 'Question') {
            $params['class'] = Question::class;
        } elseif ($params['class'] == 'Answer') {
            $params['class'] = Answer::class;
        } elseif ($params['class'] == 'QuestionCategory') {
            $params['class'] = LegacyQuestionCategory::class;
        }
    }

    private function getListModuleBeforeRenderSection(&$params = null)
    {
        $translator = $this->container->get('translator');

        $questionMessages = [
            '',
            $translator->trans('Topic successfully updated'),
            $translator->trans('Topic successfully deleted!'),
        ];

        $answerMessages = [
            '',
            $translator->trans('Answer successfully updated'),
            $translator->trans('Answer successfully deleted!'),
        ];

        if (is_numeric($_GET['message']) && isset(${$params['manageModule'].'Messages'}[$_GET['message']])) {
            echo '<p class="alert alert-success">'.${$params['manageModule'].'Messages'}[$_GET['message']].'</p>';
        }
    }

    private function getSideBarContentAfterRenderBlogItem(&$params = null)
    {
        echo $this->container->get('templating')->render('CommunityForumBundle::sitemgr-menu.html.twig');
    }

    private function getClassAccountBeforeDelete(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Answer WHERE account_id = :account_id');
        $statement->bindValue('account_id', $params['that']->id);
        $statement->execute();

        $statement = $connection->prepare('UPDATE Question SET account_id = NULL WHERE account_id = :account_id');
        $statement->bindValue('account_id', $params['that']->id);
        $statement->execute();
    }

    private function getLanguageSitemgrBeforeLoadDefines(&$params = null, $localeAcronimn)
    {
        if (empty($localeAcronimn) || ! in_array($localeAcronimn, ['en','de','es','tr','it','pt','fr']) ){
            $localeAcronimn = 'en';
        }
        if (!empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $translator = $this->container->get('translator');
                if (!empty($translator)) {
                    define('LANG_SITEMGR_QUESTION_CATEGORY_NORECORD', $translator->trans('No forum topic categories.',[],'messages', $localeAcronimn));
                    define('LANG_SITEMGR_QUESTION_ERROR_MAXIMUM_CATEGORIES', $translator->trans('The following topics exceeded the maximum number of categories:',[],'messages', $localeAcronimn));
                    define('LANG_SITEMGR_EMAILNOTIF_TYPE_100', $translator->trans('New topic answer',[],'messages', $localeAcronimn));
                    define('LANG_SITEMGR_EMAILNOTIF_DESC_100', $translator->trans('Email sent to the user when receives a new topic answer',[],'messages', $localeAcronimn));
                }
                unset($translator);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLanguageSitemgrBeforeLoadDefines method of CommunityForumBundle.php', ['exception' => $e]);
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
    }

    private function getEmailEditorAfterLoadDefaulVars(&$params = null)
    {
        $translator = $this->container->get('translator');

        $params['defaultVAR'] = array_merge($params['defaultVAR'], [
            'TOPIC_ANSWER' => $translator->trans('Answer sent by user.'),
            'PROFILE_URL'          => $translator->trans('Profile url: ("profile").'),
        ]);
    }

    private function getEmailNotificationServiceConstruct(&$params = null)
    {
        $params['placeholdersDictionaries'][] = 'TOPIC_ANSWER';
        $params['placeholdersDictionaries'][] = 'PROFILE_URL';

        $params['placeholder']['PROFILE_URL'] = 'profile';
    }

    private function getCategoryCodeBeforeInitializeObjectOnFormValidate(&$params = null)
    {
        switch ($params['obj']) {
            case 'QuestionCategory' :
                $params['obj'] = LegacyQuestionCategory::class;
                break;
        }
    }

    private function getCategoryCodeAfterSave(&$params = null)
    {
        switch ($params['obj']) {
            case 'QuestionCategory' :
                if(!empty($_POST['id']) && is_numeric($_POST['id'])) {
                    /** @var QuestionCategoryService $questionCategoryService */
                    $questionCategoryService = $this->container->get('question.category.service');
                    if ($questionCategoryService !== null) {
                        /** @var DoctrineRegistry $doctrine */
                        $doctrine = $this->container->get('doctrine');
                        if ($doctrine !== null) {
                            /** @var QuestionCategoryRepository $questionCategoryRepository */
                            $questionCategoryRepository = $doctrine->getRepository('CommunityForumBundle:QuestionCategory');
                            if ($questionCategoryRepository !== null) {
                                /** @var QuestionCategory $questionCategory */
                                $questionCategory = $questionCategoryRepository->find($_POST['id']);
                                $questionCategoryService->updateCategoryTreeRelatedEntitiesFullText($questionCategory);
                            }
                            unset($questionCategoryRepository);
                        }
                        unset($doctrine);
                    }
                    unset($questionCategoryService);
                }
                break;
        }
    }

    private function getCategoryCodeBeforeInitializeObjectOnRemoveImage(&$params = null)
    {
        switch ($params['obj']) {
            case 'QuestionCategory' :
                $params['obj'] = LegacyQuestionCategory::class;
                break;
        }
    }

    private function getFormFacebookLoginAfterBuildRedirect(&$params = null)
    {
        if ($_GET['title']) {
            $params['urlRedirect'] .= '&title='.$_GET['title'];
        }
        if ($_GET['description']) {
            $params['urlRedirect'] .= '&description='.$_GET['description'];
        }
        if ($_GET['category']) {
            $params['urlRedirect'] .= '&category='.$_GET['category'];
        }
    }

    private function getFormGoogleLoginAfterBuildRedirect(&$params = null)
    {
        if ($_GET['title']) {
            $params['urlRedirect'] .= '&title='.$_GET['title'];
        }
        if ($_GET['description']) {
            $params['urlRedirect'] .= '&description='.$_GET['description'];
        }
        if ($_GET['category']) {
            $params['urlRedirect'] .= '&category='.$_GET['category'];
        }
    }

    private function getProfileHomepageAfterRender(&$params = null)
    {
        $account_id = $params['id'];

        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        if (!empty($account_id)) {

            $query = '
                SELECT
                    Que.*,
                    (SELECT count(*) FROM Answer WHERE question_id = Que.id) as answers,
                    (SELECT entered FROM Answer WHERE question_id = Que.id ORDER BY id DESC LIMIT 1 ) as last_answer
                FROM
                    Question as Que
                WHERE
                    Que.account_id = :accountId
                ORDER BY
                    Que.entered DESC';

            $statement = $connection->prepare($query);
            $statement->bindValue('accountId', $account_id);
            $statement->execute();

            $questions = $statement->fetchAll();

            $answers = $this->container->get('doctrine')->getRepository('CommunityForumBundle:Answer')->findBy(['accountId' => $account_id]);

            $context = $this->container->get('router')->getContext();
            $context->setHost($this->container->get('kernel')->getDomain());

            echo $this->container->get('templating')->render('CommunityForumBundle::profile-qa-list.html.twig', [
                'questions' => $questions,
                'answers'   => $answers,
                'accountId' => $account_id,
                'sessionId' => $_SESSION['SESS_ACCOUNT_ID'],
            ]);

        }
    }

    private function getSearchEngineBeforeReturnElasticType(&$params = null)
    {
        if ($params['module'] == 'forum' || $params['module'] == 'question') {
            $params['return'] = QuestionConfiguration::$elasticType;
        }
    }

    private function getSearchEngineBeforeReturnModuleAlias(&$params = null)
    {
        if ($params['module'] == 'forum' || $params['module'] == 'question') {
            $params['return'] = $this->container->getParameter('alias_forum_module');
        }
    }

    private function getModuleFilterBeforeSetupFilterView(&$params = null)
    {
        if($params['module'] == 'forum')
        {
            $params['module'] = 'question';
        }
    }

    private function getWysiwygExtensionBeforeValidateWidget(&$params = null)
    {
        switch ($params['widgetFile']) {
            case '::widgets/page-editor/forum/forum-detail.html.twig':
                $params['widgetFile'] = 'CommunityForumBundle::forum-detail-content.html.twig';
                break;

            case '::widgets/page-editor/forum/horizontal-question-bar.html.twig':
                $params['widgetFile'] = 'CommunityForumBundle::horizontal-question-bar.html.twig';
                break;

            case '::widgets/page-editor/forum/two-columns-recent-questions.html.twig':
                $params['widgetFile'] = 'CommunityForumBundle::two-columns-recent-questions.html.twig';
                break;
        }
    }

    private function getSummaryExtensionBeforeRenderSummary(&$params = null)
    {
        switch ($params['twigFile']) {

            case '::modules/question/summary.html.twig':
                $params['twigFile'] = 'CommunityForumBundle::summary.html.twig';
                break;

        }
    }

    private function getLoginCodeBeforeLoginRedirect(&$params = null)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->cookies->has('forum_info')) {

            $data = unserialize($this->container->get('request_stack')->getCurrentRequest()->cookies->get('forum_info'));

            if (!empty($data)) {

                $doctrine = $this->container->get('doctrine');
                $manager = $doctrine->getManager();

                $response = new Response();
                $response->headers->setCookie(new Cookie('forum_info', ''));
                $response->prepare(Request::createFromGlobals());
                $response->sendHeaders();

                $friendlyUrl = trim(substr(strip_tags($data['title']), 0, 50));
                $friendlyUrl = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $friendlyUrl);
                $friendlyUrl = strtolower(trim($friendlyUrl, '-'));
                $friendlyUrl = preg_replace("/[\/_| -]+/", '-', $friendlyUrl);

                $invalidFriendly = $doctrine->getRepository('CommunityForumBundle:Question')->findOneBy([
                    'friendlyUrl' => $friendlyUrl,
                ]);

                if ($invalidFriendly) {
                    $friendlyUrl .= '-'.uniqid();
                }

                $account = $doctrine->getRepository('WebBundle:Accountprofilecontact')->find($_SESSION['SESS_ACCOUNT_ID']);

                $question = new Entity\Question();

                $question->setTitle($data['title']);
                $question->setDescription($data['description']);
                $question->setAccount($account);
                if ($data['category']) {
                    $category = $doctrine->getRepository('CommunityForumBundle:QuestionCategory')->find($data['category']);
                    $question->setCategory($category);
                }
                $question->setFriendlyUrl($friendlyUrl);
                $question->setEntered(new DateTime('now'));
                $question->setUpdated(new DateTime('now'));
                $question->setUpvotes(0);
                $question->setStatus('A');

                $manager->persist($question);
                $manager->flush();

                $this->container->get('question.synchronization')->addUpsert($question->getId());

                $_GET['userperm'] = false;
                $params['url'] = $this->container->get('router')->generate('forum_detail', [
                    'friendlyUrl' => $friendlyUrl,
                    '_format'     => 'html',
                ]);
            }

        }
    }

    private function getAccountCodeBeforeRedirect(&$params = null)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->cookies->has('forum_info')) {

            $data = unserialize($this->container->get('request_stack')->getCurrentRequest()->cookies->get('forum_info'));

            if (!empty($data)) {

                $doctrine = $this->container->get('doctrine');
                $manager = $doctrine->getManager();

                $response = new Response();
                $response->headers->setCookie(new Cookie('forum_info', ''));
                $response->prepare(Request::createFromGlobals());
                $response->sendHeaders();

                $friendlyUrl = trim(substr(strip_tags($data['title']), 0, 50));
                $friendlyUrl = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $friendlyUrl);
                $friendlyUrl = strtolower(trim($friendlyUrl, '-'));
                $friendlyUrl = preg_replace("/[\/_| -]+/", '-', $friendlyUrl);

                $invalidFriendly = $doctrine->getRepository('CommunityForumBundle:Question')->findOneBy([
                    'friendlyUrl' => $friendlyUrl,
                ]);

                if ($invalidFriendly) {
                    $friendlyUrl .= '-'.uniqid();
                }

                $account = $doctrine->getRepository('WebBundle:Accountprofilecontact')->find($_SESSION['SESS_ACCOUNT_ID']);

                $question = new Entity\Question();

                $question->setTitle($data['title']);
                $question->setDescription($data['description']);
                $question->setAccount($account);
                if ($data['category']) {
                    $category = $doctrine->getRepository('CommunityForumBundle:QuestionCategory')->find($data['category']);
                    $question->setCategory($category);
                }
                $question->setFriendlyUrl($friendlyUrl);
                $question->setEntered(new DateTime('now'));
                $question->setUpdated(new DateTime('now'));
                $question->setUpvotes(0);
                $question->setStatus('A');

                $manager->persist($question);
                $manager->flush();

                $this->container->get('question.synchronization')->addUpsert($question->getId());

                $_GET['userperm'] = false;
                $params['location'] = $this->container->get('router')->generate('forum_detail', [
                    'friendlyUrl' => $friendlyUrl,
                    '_format'     => 'html',
                ]);
            }

        }
    }

    private function getCategoryHelperBeforeReturnCategoryRepository(&$params = null)
    {
        if (strtolower($params['module']) == 'forum' || strtolower($params['module']) == 'question') {
            $params['module'] = 'article';
        }
    }

    // Todo: methods to hooks revision names

    private function getQuestionForm(&$params = null)
    {
        $translator = $this->container->get('translator');
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $question = $this->container->get('modstore.storage.service')->retrieve('question');

        $statement = $connection->prepare("SELECT id, title FROM QuestionCategory WHERE enabled = 'y' ORDER BY title");
        $statement->execute();

        $result = $statement->fetchAll();

        $categoriesTitle = [];
        $categoriresId = [];

        foreach ($result as $row) {
            $categoriesTitle[] = $row['title'];
            $categoriresId[] = $row['id'];
        }

        $categories = html_selectBox(
            'category_id',
            $categoriesTitle,
            $categoriresId,
            $question->category_id,
            '',
            'class="form-control status-select"',
            $translator->trans('Select topic category')
        );

        echo $this->container->get('templating')->render('CommunityForumBundle::form-question.html.twig', [
            'question'   => $question,
            'categories' => $categories,
        ]);
    }

    private function getForumQuestionToProfile(&$params = null)
    {
        $question = $this->container->get('modstore.storage.service')->retrieve('question');

        echo $this->container->get('templating')->render('CommunityForumBundle::form-question-profile.html.twig', [
            'question'   => $question
        ]);
    }

    private function getForumAnswerFormToProfile(&$params = null)
    {
        $question = $this->container->get('modstore.storage.service')->retrieve('question');
        $answer = $this->container->get('modstore.storage.service')->retrieve('answer');

        echo $this->container->get('templating')->render('CommunityForumBundle::form-answer-profile.html.twig', [
            'question' => $question,
            'answer'   => $answer,
        ]);
    }

    private function getForumModalDelete(&$params = null)
    {
        $translator = $this->container->get('translator');

        if (string_strpos($_SERVER['PHP_SELF'], '/content/forum/') !== false) {
            $params['modalTitle'] = $translator->trans("Delete", array(), "system").' '.$translator->trans('Topic');
            $params['modalMessage'] = $translator->trans('Are you sure?');
        } elseif (string_strpos($_SERVER['PHP_SELF'], '/content/forum/answers/') !== false) {
            $params['modalTitle'] = $translator->trans("Delete", array(), "system").' '.$translator->trans('Answer');
            $params['modalMessage'] = $translator->trans('Are you sure?');
        }
    }

    private function getLegacySitemgrContentForumReportAfterCheckPermissions(&$params = null)
    {
        $trans = $this->container->get('translator');
        if (!empty($params['id']) && is_numeric($params['id'])) {
            $params['question'] = new Question($params['id']);
            $currentIsoLang = $this->getCurrentISOLang();
            $this->container->get('modstore.storage.service')->store('question', $params['question']);
            $params['statusName'] = (($params['question']->getString('status')!=='A')?($trans->trans("Not",array(),'system', $currentIsoLang) . " "):"") . $trans->trans("Active",array(),'system', $currentIsoLang);

            $questionReport = array();
            $doctrine = $this->container->get("doctrine");
            $lastDayOfLastMonth = new Datetime('last day of last month');
            $lastDayOfCurrentMonth = new Datetime('last day');

            /* Past monthly sum */
            $reportQuestionMonthlyRepository = $doctrine->getRepository("CommunityForumBundle:ReportQuestionMonthly");
            /** @var QueryBuilder $reportQuestionMonthlyQueryBuilder */
            $reportQuestionMonthlyQueryBuilder = $reportQuestionMonthlyRepository->createQueryBuilder('rqm');
            $reportQuestionMonthlyQueryBuilder = $reportQuestionMonthlyQueryBuilder
                ->where(
                    $reportQuestionMonthlyQueryBuilder->expr()->andX(
                        $reportQuestionMonthlyQueryBuilder->expr()->eq('rqm.questionId', ':questionId'),
                        $reportQuestionMonthlyQueryBuilder->expr()->lte('rqm.day',':lastDayOfLastMonth')
                    )
                )
                ->setParameter('questionId', $params['id'])
                ->setParameter('lastDayOfLastMonth', $lastDayOfLastMonth);

            $reportQuestionMonthlyQueryBuilder = $reportQuestionMonthlyQueryBuilder
                ->addSelect('CONCAT(YEAR(rqm.day),\'-\',MONTH(rqm.day)) AS period')
                ->addSelect('SUM(rqm.summaryView) AS summary')
                ->addSelect('SUM(rqm.detailView) AS detail')
                ->groupBy('period')
                ->orderBy('period','DESC');

            $reportQuestionMonthlyQuery = $reportQuestionMonthlyQueryBuilder->getQuery();
            $reportQuestionMonthlyQueryArrayResult = $reportQuestionMonthlyQuery->getArrayResult();

            /** @var ReportQuestion $reportQuestionToday **/
            foreach($reportQuestionMonthlyQueryArrayResult as $reportQuestionMonthly) {
                $questionReport[$reportQuestionMonthly['period']]['summary'] = $reportQuestionMonthly['summary'];
                $questionReport[$reportQuestionMonthly['period']]['detail'] = $reportQuestionMonthly['detail'];
            }

            /* Initiate data for current month */
            $currentMonthPeriod = date('Y') . '-' . date('n');
            $questionReport[$currentMonthPeriod]['summary'] = 0;
            $questionReport[$currentMonthPeriod]['detail']  = 0;

            /* Current month past day sum */
            $reportQuestionDailyRepository = $doctrine->getRepository("CommunityForumBundle:ReportQuestionDaily");
            /** @var QueryBuilder $reportQuestionDailyQueryBuilder */
            $reportQuestionDailyQueryBuilder = $reportQuestionDailyRepository->createQueryBuilder('rqd');
            $reportQuestionDailyQueryBuilder = $reportQuestionDailyQueryBuilder
                ->where(
                    $reportQuestionDailyQueryBuilder->expr()->andX()->addMultiple(array(
                        $reportQuestionDailyQueryBuilder->expr()->eq('rqd.questionId', ':questionId'),
                        $reportQuestionDailyQueryBuilder->expr()->lte('rqd.day',':lastDayOfCurrentMonth'),
                        $reportQuestionDailyQueryBuilder->expr()->gt('rqd.day',':lastDayOfLastMonth')
                    ))
                )
                ->setParameter('questionId', $params['id'])
                ->setParameter('lastDayOfLastMonth', $lastDayOfLastMonth)
                ->setParameter('lastDayOfCurrentMonth', $lastDayOfCurrentMonth);

            $reportQuestionDailyQueryBuilder = $reportQuestionDailyQueryBuilder
                ->addSelect('CONCAT(YEAR(rqd.day),\'-\',MONTH(rqd.day)) AS period')
                ->addSelect('SUM(rqd.summaryView) AS summary')
                ->addSelect('SUM(rqd.detailView) AS detail')
                ->groupBy('period');
            $reportQuestionDailyQuery = $reportQuestionDailyQueryBuilder->getQuery();
            $reportQuestionDailyQueryArrayResult = $reportQuestionDailyQuery->getArrayResult();
            /** @var ReportQuestion $reportQuestionToday **/
            foreach($reportQuestionDailyQueryArrayResult as $reportQuestionDaily) {
                $questionReport[$currentMonthPeriod]['summary'] += $reportQuestionDaily['summary'];
                $questionReport[$currentMonthPeriod]['detail'] += $reportQuestionDaily['detail'];
            }

            /* Current month actual day sum */
            $reportQuestionRepository = $doctrine->getRepository("CommunityForumBundle:ReportQuestion");
            /** @var QueryBuilder $reportQuestionQueryBuilder */
            $reportQuestionQueryBuilder = $reportQuestionRepository->createQueryBuilder('rq');
            $reportQuestionQueryBuilder = $reportQuestionQueryBuilder
                ->where(
                    $reportQuestionQueryBuilder->expr()->andX(
                        $reportQuestionQueryBuilder->expr()->eq('rq.questionId', ':questionId'),
                        $reportQuestionQueryBuilder->expr()->gt('rq.date',':lastDayOfCurrentMonth')
                    )
                )
                ->setParameter('questionId', $params['id'])
                ->setParameter('lastDayOfCurrentMonth', $lastDayOfCurrentMonth);
            $reportQuestionQueryBuilder = $reportQuestionQueryBuilder
                ->addSelect('CONCAT(YEAR(rq.date),\'-\',MONTH(rq.date)) AS period')
                ->addSelect('rq.reportType AS report_type')
                ->addSelect('SUM(rq.reportAmount) AS amount')
                ->groupBy('period, report_type');

            $reportQuestionTodayQuery = $reportQuestionQueryBuilder->getQuery();
            $reportQuestionTodayQueryArrayResult = $reportQuestionTodayQuery->getArrayResult();
            /** @var ReportQuestion $reportQuestionToday **/
            foreach($reportQuestionTodayQueryArrayResult as $reportQuestionToday){
                switch ($reportQuestionToday['report_type']){
                    case ReportHandlerWithCommunityForum::QUESTION_SUMMARY:
                        $questionReport[$currentMonthPeriod]['summary'] += $reportQuestionToday['amount'];
                        break;
                    case ReportHandlerWithCommunityForum::QUESTION_DETAIL:
                        $questionReport[$currentMonthPeriod]['detail'] += $reportQuestionToday['amount'];
                        break;
                }
            }
            $params['reports'] = $questionReport;
        } else {
            header('Location:' . $params['url_redirect']);
            exit;
        }
    }

    private function getLegacySitemgrContentForumReportAfterCheckRegistration(&$params = null)
    {
        $url_base = $params['url_base'];
        $url_redirect = $params['url_redirect'];
        $reports = $params['reports'];
        $status = $params['status'];
        $statusName = $params['statusName'];
        $question = $params['question'];

        echo $this->container->get('twig')->render('CommunityForumBundle::form-question-report.html.twig', [
            'defaultUrl' => $url_base,
            'redirectUrl' => $url_redirect,
            'questionTitle'   => $question->getString('title'),
            'statusName' => $statusName,
            'reports' => $reports,
            'lang' => $this->getCurrentISOLang()
        ]);
    }

    private function getQuestionManager(&$params = null)
    {
        if (isset($_REQUEST['id'])) {
            $question = new Question($_REQUEST['id']);

            $this->container->get('modstore.storage.service')->store('question', $question);
        } else {
            header('Location:'.$params['url_redirect']);
            exit;
        }

        if ($_POST) {
            $question->title = $_POST['title'];
            $question->description = $_POST['description'];
            $question->category_id = $_POST['parent_id'];
            $question->setString('updated', date('Y-m-d H:i:s'));
            $question->Save();
            if (isset($params['is_member']) && $params['is_member']) {
                header('Location:'.$params['url_redirect']);
            } else {
                header('Location:'.$params['url_redirect'].'/index.php?id='.$_REQUEST['id'].'&module=question&process=&newest=&message=1&screen=&letter=');
            }
            exit;
        }
    }

    private function getAnswerManager(&$params = null)
    {
        if (isset($_REQUEST['id'])) {
            $answer = new Answer($_REQUEST['id']);
            $question = new Question($answer->question_id);

            $this->container->get('modstore.storage.service')->store('question', $question);
            $this->container->get('modstore.storage.service')->store('answer', $answer);
        } else {
            header('Location:'.$params['url_redirect']);
            exit;
        }

        if ($_POST) {
            $answer->description = $_POST['description'];
            $answer->setString('updated', date('Y-m-d H:i:s'));
            $answer->Save();

            if (isset($params['is_member']) && $params['is_member']) {
                header('Location:'.$params['url_redirect']);
            } else {
                header('Location:'.$params['url_redirect'].'/index.php?id='.$_REQUEST['id'].'&module=answer&process=&newest=&message=1&screen=&letter=');
            }
            exit;
        }
    }

    private function getAnswerForm(&$params = null)
    {
        $question = $this->container->get('modstore.storage.service')->retrieve('question');
        $answer = $this->container->get('modstore.storage.service')->retrieve('answer');

        echo $this->container->get('templating')->render('CommunityForumBundle::form-answer.html.twig', [
            'question' => $question,
            'answer'   => $answer,
        ]);
    }

    private function getLoadWidgetAfterAddStandardWidget(&$params = null)
    {
        $trans = $this->container->get('translator');
        $params['standardWidgets'][] = [
            'title' => $trans->trans('Forum Detail', [], 'widgets', 'en'),
            'twigFile' => '/forum/forum-detail.html.twig',
            'type' => 'forum',
            'content'  => [
                'labelCategories'     => 'Categories',
                'labelPopularQuestions'   => 'Popular topics',
            ],
            'modal' => '',
        ];
        $params['standardWidgets'][] = [
            'title' => $trans->trans('Horizontal Question Bar', [], 'widgets', 'en'),
            'twigFile' => '/forum/horizontal-question-bar.html.twig',
            'type' => 'forum',
            'content' => [],
            'modal' => '',
        ];
        $params['standardWidgets'][] = [
            'title' => $trans->trans('Two columns recent questions', [], 'widgets', 'en'),
            'twigFile' => '/forum/two-columns-recent-questions.html.twig',
            'type' => 'forum',
            'content'  => [
                'labelCategories'     => $trans->trans('Categories', [], 'widgets', 'en'),
                'labelPopularQuestions'   => $trans->trans('Popular topics', [], 'widgets', 'en'),
                'hasDesign'           => 'false',
                'backgroundColor'     => 'brand',
            ],
            'modal'    => 'edit-generic-modal',
        ];
    }

    private function getThemeServiceAfterAddCommonWidgets(&$params = null)
    {
        $trans = $this->container->get('translator');
        $params['widget'][] = $trans->trans('Forum Detail', [], 'widgets', 'en');
        $params['widget'][] = $trans->trans('Horizontal Question Bar', [], 'widgets', 'en');
        $params['widget'][] = $trans->trans('Two columns recent questions', [], 'widgets', 'en');
    }

    private function getLoadPageTypeAfterAddPageTypes(&$params = null)
    {
        $trans = $this->container->get('translator');
        $params['standardPageTypes'][] = [
            'title' => $trans->trans('Forum Homepage', [], 'widgets', 'en'),
        ];
        $params['standardPageTypes'][] = [
            'title' => $trans->trans('Forum Detail', [], 'widgets', 'en'),
        ];
    }

    private function getLoadPageAfterAddPages(&$params = null)
    {
        $trans = $this->container->get('translator');

        $params['standardPages'][] =
            [
                'title' => $trans->trans('Forum Homepage', [], 'widgets', 'en'),
                'url' => $this->container->getParameter('alias_forum_module'),
                'metaDesc' => '',
                'metaKey' => '',
                'sitemap' => false,
                'customTag' => '',
                'pageType' => $params['that']->getReference('TYPE_' . $trans->trans('Forum Homepage', [], 'widgets', 'en')),
            ];

        $params['standardPages'][] = [
            'title' => $trans->trans('Forum Detail', [], 'widgets', 'en'),
            'url' => null,
            'metaDesc' => '',
            'metaKey' => '',
            'sitemap' => true,
            'customTag' => '',
            'pageType' => $params['that']->getReference('TYPE_' . $trans->trans('Forum Detail', [], 'widgets', 'en')),
        ];
    }
}
