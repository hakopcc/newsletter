<?php
declare(strict_types=1);

namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerDataArray;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\FacebookMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerDataClassesArray;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerDomainSetting;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerFloatingButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonDataArray;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\TelegramData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\WhatsappData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\ListingInstantMessenger;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Exceptions\InstantMessengerServiceException;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Repository\ListingLevelInstantMessengerRepository;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services\FacebookHelper;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services\InstantMessengerService;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use ArcaSolutions\WysiwygBundle\Repository\PageWidgetRepository;
use ArcaSolutions\WysiwygBundle\Repository\WidgetRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

/**
 * Class InstantMessengerIntegrationBundle
 * @package ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration
 */
class InstantMessengerIntegrationBundle extends Bundle
{
    private $devEnvironment = false;

    /**
     * @param $logger
     * @param $notLoggedCriticalException
     * @param string $logErrorMessage
     * @param bool $resultValue
     * @return bool
     */
    private static function foundJsonError(Logger $logger, &$notLoggedCriticalException, string $logErrorMessage, &$resultValue = false): bool
    {
        $returnValue = false;
        if ($resultValue === false && json_last_error() !== JSON_ERROR_NONE) {
            $returnValue = true;
            $e = new Exception(json_last_error_msg(), json_last_error());
            if ($logger !== null) {
                $logger->critical($logErrorMessage, ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
            unset($e);
        }
        return $returnValue;
    }

    /**
     * Method just to allow JMSTranslator include know strings that was not used directly by a trans method call or by a trans twig extension
     */
    private function dummyMethodToIncludeTranslatableString(){
        return;
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');
        if ($translator !== null) {
            $translator->trans('hasInstantMessenger', array(), 'advertise');
        }
        unset($translator);
    }

    /**
     * boot - Boots the bundle.
     *
     * @throws Exception
     */
    public function boot(): void
    {
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $this->devEnvironment = Kernel::ENV_DEV === $this->container->getParameter('kernel.environment');
            if ($this->isSitemgr()) {
                /*
                 * Register sitemgr only bundle hooks
                 */

                /*
                 * Listing level related Hooks registering - Begin
                 */
                Hooks::Register('classlistinglevel_contruct', function (&$params = null) {
                    $this->getClassListingLevelConstruct($params);
                });

                Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                    $this->getFormPricingAfterAddFields($params);
                });

                Hooks::Register('paymentgateway_after_save_listinglevel', function (&$params = null) {
                    $this->getPaymentGatewayAfterSaveListingLevel($params);
                });

                /*
                 * Listing level related Hooks registering - End
                 */

                /*
                 * Listing related Hooks registering - Begin
                 */

                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    $this->getListingCodeAfterSave($params);
                });

                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    $this->getClassListingBeforeDelete($params);
                });

                Hooks::Register('listingbundle-controller-getlistinglevelfieldsaction_after_getlistinglevelfieldsnamebylevel', function (&$params = null){
                    $this->getListingBundleControllerGetlistingLevelFieldsActionAfterGetListingLevelFieldsNameByLevel($params);
                });

                Hooks::Register('listingform_after_contact_information_panel', function (&$params = null) {
                    $this->getListingFormAfterContactInformationPanel($params);
                });

                Hooks::Register('legacy-sitemgr-content-listing_before_modal-includes', function (&$params = null) {
                    $this->getLegacySitemgrContentListingBeforeModalIncludes($params);
                });

                Hooks::Register('legacy-sitemgr-configuration-basicinformation_before_main-content-closetag', function (&$params = null) {
                    $this->getLegacySitemgrConfigurationBasicInformationBeforeMainContentCloseTag($params);
                });

                Hooks::Register('validatefunct_validate_listing', function (&$params = null) {
                    $this->getValidateFunctValidateListing($params);
                });

                Hooks::Register('listingcode_after_setup_form', function (&$params = null) {
                    $this->getListingCodeAfterSetupForm($params);
                });

                Hooks::Register('systemfunct_after_setup_gamefyitemsfields', function (&$params = null) {
                    $this->getSystemFunctAfterSetupGamefyItemsFields($params);
                });
                Hooks::Register('systemfunct_after_setup_gamefyitemsactivated', function (&$params = null) {
                    $this->getSystemFunctAfterSetupGamefyItemsActivated($params);
                });

                /*
                 * Listing related Hooks registering - End
                 */

                /*
                 * Sponsors/Site Manager instant messaging related template Hooks registering - Begin
                 */
                Hooks::Register('legacy_instant_messaging_panel_body_twig', function (&$params = null) {
                    $this->getLegacyInstantMessagingPanelBodyTwig($params);
                });

                Hooks::Register('legacy_instant_messaging_panel_footer_twig', function (&$params = null) {
                    $this->getLegacyInstantMessagingPanelFooterTwig($params);
                });
                /*
                 * Sponsors/Site Manager instant messaging related template Hooks registering - End
                 */

                /*
                 * Site manager general settings related Hooks registering - Begin
                 */
                Hooks::Register('generalsettings_after_save', function (&$params = null) {
                    $this->getGeneralSettingsAfterSave($params);
                });
                Hooks::Register('generalsettings_after_render_form', function (&$params = null) {
                    $this->getGeneralSettingsAfterRenderForm($params);
                });
                /*
                 * Site manager general settings related Hooks registering - End
                 */

                /*
                 * Site manager basic settings related Hooks registering - Begin
                 */

                Hooks::Register('sitemgr_configuration_basic_information_after_include_form_siteinfo', function (&$params = null) {
                    $this->getSitemgrConfigurationBasicInformationAfterIncludeFormSiteinfo($params);
                });

                Hooks::Register('sitemgr_code_content_basic_settings_post_request_handle_before_check_success', function (&$params = null) {
                    $this->getSitemgrCodeContentBasicSettingsPostRequestHandleBeforeCheckSuccess($params);
                });

                Hooks::Register('sitemgr_code_content_basic_settings_post_request_handle_after_register_success', function (&$params = null) {
                    $this->getSitemgrCodeContentBasicSettingsPostRequestHandleAfterRegisterSuccess($params);
                });

                Hooks::Register('formdesignsettings_after_render_header_specific_block', function (&$params = null) {
                    $this->getFormDesignSettingsAfterRenderHeaderSpecificBlock($params);
                });

                Hooks::Register('formdesignsettings_after_render_footer_specific_block', function (&$params = null) {
                    $this->getFormDesignSettingsAfterRenderFooterSpecificBlock($params);
                });

                Hooks::Register('widgetactionajax_after_load', function (&$params = null) {
                    $this->getWidgetActionAjaxAfterLoad($params);
                });

                Hooks::Register('widgetactionajax_before_save', function (&$params = null) {
                    $this->getWidgetActionAjaxBeforeSave($params);
                });

                Hooks::Register('widgetactionajax_after_save', function (&$params = null) {
                    $this->getWidgetActionAjaxAfterSave($params);
                });

                Hooks::Register('editcontactformmodal_after_render_generic_inputs', function (&$params = null) {
                    $this->getEditContactFormModalAfterRenderGenericInputs($params);
                });

                Hooks::Register('widgetservice_get_generic_label_inputs_after_set_exceptions_keys', function (&$params = null) {
                    $this->getWidgetServiceGetGenericLabelInputsAfterSetExceptionsKeys($params);
                });

                /*
                 * Site manager basic settings related Hooks registering - End
                 */
            } else {
                /*
                 * Sponsors/Site Manager instant messaging related template Hooks registering - Begin
                 */
                Hooks::Register('legacy_instant_messaging_panel_body_twig', function (&$params = null) {
                    $this->getLegacyInstantMessagingPanelBodyTwig($params, true);
                });

                Hooks::Register('legacy_instant_messaging_panel_footer_twig', function (&$params = null) {
                    $this->getLegacyInstantMessagingPanelFooterTwig($params);
                });
                /*
                 * Sponsors/Site Manager instant messaging related template Hooks registering - End
                 */

                /*
                * Register front only bundle hooks (and also the sponsor ones)
                */
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    $this->getClassListingBeforeDelete($params);
                });

                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    $this->getListingCodeAfterSave($params);
                });

                Hooks::Register('listinglevel_construct', function (&$params = null) {
                    $this->getListingLevelConstruct($params);
                });
                Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                    $this->getListingLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('listingdetail_before_address', function (&$params = null) {
                    $this->getListingDetailBeforeAddress($params);
                });
                Hooks::Register('listingsummary_after_additional_phone', function (&$params = null) {
                    $this->getListingSummaryAfterAdditionalPhone($params);
                });

                Hooks::Register('legacy-sponsors-listing_before_modal-includes', function (&$params = null) {
                    $this->getLegacySponsorsListingBeforeModalIncludes($params);
                });

                Hooks::Register('listingbundle-controller-getlistinglevelfieldsaction_after_getlistinglevelfieldsnamebylevel', function (&$params = null){
                    $this->getListingBundleControllerGetlistingLevelFieldsActionAfterGetListingLevelFieldsNameByLevel($params);
                });

                Hooks::Register('listingform_after_contact_information_panel', function (&$params = null) {
                    $this->getListingFormAfterContactInformationPanel($params, true);
                });

                Hooks::Register('systemfunct_after_setup_gamefyitemsfields', function (&$params = null) {
                    $this->getSystemFunctAfterSetupGamefyItemsFields($params);
                });
                Hooks::Register('systemfunct_after_setup_gamefyitemsactivated', function (&$params = null) {
                    $this->getSystemFunctAfterSetupGamefyItemsActivated($params);
                });
                Hooks::Register('wysiwyg_extension_renderpage_after_render_widgets', function (&$params = null) {
                    $this->getWysiwygExtensionRenderpageAfterRenderWidgets($params);
                });

                Hooks::Register('header_type3_navbar_mobile_overwrite_phone', function (&$params = null) {
                    $this->getHeaderType3NavbarOverwritePhone($this::HEADER_TYPE_3_NAVBAR_TYPE_MOBILE, $params);
                });

                Hooks::Register('header_type3_navbar_outside_overwrite_phone', function (&$params = null) {
                    $this->getHeaderType3NavbarOverwritePhone($this::HEADER_TYPE_3_NAVBAR_TYPE_OUTSIDE, $params);
                });

                Hooks::Register('views-blocks-contactus_willrender', function (&$params = null) {
                    $this->getViewsBlocksContactUsWillRender($params);
                });

                Hooks::Register('contactus_after_phone_rendering', function (&$params = null) {
                    $this->getContactUsAfterPhoneRendering($params);
                });

                Hooks::Register('contact_form_after_phone_rendering', function (&$params = null) {
                    $this->getContactFormAfterPhoneRendering($params);
                });

                Hooks::Register('footer_type3_footer_contact_after_phone_rendering', function (&$params = null) {
                    $this->getFooterType3FooterContactAfterPhoneRendering($params);
                });

                Hooks::Register('contact_form_willrender_contactus-info', function (&$params = null) {
                    $this->getContactFormWillRenderContactUsInfo($params);
                });
            }
            parent::boot();
        } catch (Exception $e) {
            if ($logger!==null) {
                $logger->critical('Unexpected error on boot method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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

    /**
     * getListingLevelConstruct - Inserts in the listing level class the parameter that indicates if the level was instantMessenger integration or not.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getListingLevelConstruct(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $params['that']->hasInstantMessenger = false;
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getListingLevelConstruct method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getListingLevelFeatureBeforeReturn - Populates the listing level class parameter that indicates if the level was instantMessenger integration or not.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getListingLevelFeatureBeforeReturn(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $listingLevel = $params['listingLevel'];
                if (!empty($listingLevel)) {
                    $doctrine = $this->container->get('doctrine');
                    if (!empty($doctrine)) {
                        $listingLevelInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                        if (!empty($listingLevelInstantMessengerRepository)) {
                            $level = $listingLevel->level;
                            if (!empty($level)) {
                                $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy([
                                    'level' => $level,
                                ]);
                                if (!empty($listingLevelInstantMessenger)) {
                                    $params['listingLevel']->hasInstantMessenger = 'y' === $listingLevelInstantMessenger->getInstantMessenger();
                                }
                                unset($listingLevelInstantMessenger);
                            }
                            unset($level);
                        }
                        unset($listingLevelInstantMessengerRepository);
                    }
                    unset($doctrine);
                }
                unset($listingLevel);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getListingLevelFeatureBeforeReturn method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getGeneralSettingsAfterSave - Save instant messenger integration plugin related data after save general settings on the site manager.
     *
     * @param null $params array (includes the following keys: http_post_array, http_get_array, success, error)
     *
     * @throws Exception
     */
    private function getGeneralSettingsAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (isset($params['http_post_array'], $params['http_get_array']) && is_array($params['http_post_array']) && is_array($params['http_get_array']) && isset($params['success']) && isset($params['error'])) {
                    $httpPostArray = $params['http_post_array'];
                    $httpGetArray = $params['http_get_array'];
                    $successRef = &$params['success'];
                    if (!empty($httpPostArray)) {
                        if(array_key_exists('save_plugin',$httpPostArray)) {
                            $settings = $this->container->get('settings');
                            /** @var InstantMessengerService $instantMessengerService */
                            $instantMessengerService = $this->container->get('instantmessenger.service');
                            if (!empty($settings) && $instantMessengerService !== null) {
                                $lang = 'en';
                                /**
                                 * @var LanguageHandler $languageHandler
                                 */
                                $languageHandler = $this->container->get('languagehandler');
                                if ($languageHandler !== null) {
                                    $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                    $lang = $languageHandler->getISOLang($sitemgrLocale);
                                    unset($sitemgrLocale, $languageHandler);
                                }
                                unset($languageHandler);
                                $instantMessengerService->setLang($lang);
                                $instantMessengerDomainSettingFromHttpPostArray = new InstantMessengerDomainSetting();
                                $instantMessengerDomainSettingFromDatabase = new InstantMessengerDomainSetting();
                                $instantMessengerService->extractDomainSettingFromHttpPostArray($httpPostArray, $instantMessengerDomainSettingFromHttpPostArray);
                                $instantMessengerSiteManagerSettingsChanged = false;
                                $encodedJsonInstantMessengerSiteManagerSettings = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_settings');
                                if (!empty($encodedJsonInstantMessengerSiteManagerSettings)) {
                                    try {
                                        $instantMessengerService->extractDomainSettingFromEncodedJson($encodedJsonInstantMessengerSiteManagerSettings, $instantMessengerDomainSettingFromDatabase);
                                        $instantMessengerSiteManagerSettingsChanged = $instantMessengerService->hasDifferenceBetweenInstantMessengerDomainSettings($instantMessengerDomainSettingFromDatabase, $instantMessengerDomainSettingFromHttpPostArray);
                                    } catch (InstantMessengerServiceException $e) {
                                        if ($logger !== null) {
                                            $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                        } else {
                                            $notLoggedCriticalException = $e->getPrevious();
                                        }
                                    }
                                } else {
                                    $instantMessengerSiteManagerSettingsChanged = true;
                                }
                                unset($encodedJsonInstantMessengerSiteManagerSettings);
                                if ($instantMessengerSiteManagerSettingsChanged) {
                                    $encodedInstantMessengerIntegrationSettings = json_encode($instantMessengerDomainSettingFromHttpPostArray);
                                    if (JSON_ERROR_NONE === json_last_error()) {
                                        $settings->setSetting('plugin_instant_messenger_integration_site_manager_settings', $encodedInstantMessengerIntegrationSettings);
                                        $successRef = true;
                                    } else {
                                        $successRef = false;
                                        $e = new Exception(json_last_error_msg(), json_last_error());
                                        if ($logger !== null) {
                                            $logger->critical('Unexpected json error on getGeneralSettingsAfterSave method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                                        } else {
                                            $notLoggedCriticalException = $e;
                                        }
                                    }
                                    unset($encodedInstantMessengerIntegrationSettings);
                                }
                                unset($instantMessengerSiteManagerSettingsChanged);
                            }
                            unset($settings);
                        }
                    }
                    unset($httpPostArray,
                        $httpGetArray);
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getGeneralSettingsAfterSave method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getGeneralSettingsAfterRenderForm - Load instant messenger integrarion plugin panel to display options on the General Settings Plugins tab.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getGeneralSettingsAfterRenderForm(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (isset($params['http_post_array'], $params['http_get_array']) && is_array($params['http_post_array']) && is_array($params['http_get_array'])) {
                    $httpPostArray = $params['http_post_array'];
                    $httpGetArray = $params['http_get_array'];
                    $settings = $this->container->get('settings');
                    /** @var Twig_Environment $twig */
                    $twig = $this->container->get('twig');
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if (!empty($settings) && $twig !== null && $instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            $sitemgrLocale = $settings->getSetting('sitemgr_language');
                            $lang = $languageHandler->getISOLang($sitemgrLocale);
                            unset($sitemgrLocale, $languageHandler);
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);
                        $instantMessengerDomainSetting = new InstantMessengerDomainSetting();
                        $encodedJsonInstantMessengerSiteManagerSettings = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_settings');
                        if (!empty($encodedJsonInstantMessengerSiteManagerSettings)) {
                            try {
                                $instantMessengerService->extractDomainSettingFromEncodedJson($encodedJsonInstantMessengerSiteManagerSettings, $instantMessengerDomainSetting);
                            } catch (InstantMessengerServiceException $e) {
                                if ($logger!==null) {
                                    $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                } else {
                                    $notLoggedCriticalException = $e->getPrevious();
                                }
                            }
                        }
                        unset($encodedJsonInstantMessengerSiteManagerSettings);
                        if (!empty($httpPostArray)) {
                            $instantMessengerService->extractDomainSettingFromHttpPostArray($httpPostArray, $instantMessengerDomainSetting);
                        }
                        $floatingButtonPositionData = (object)[
                            'options' => array(
                                'bottom-left' => 'Bottom - Left',
                                'bottom-right' => 'Bottom - Right',
                            ),
                            'defaultOption' => 'bottom-left',
                        ];
                        $saveButtonData = (object)[
                            'dataLoadingText' => LANG_LABEL_FORM_WAIT,
                            'label' => LANG_SITEMGR_SAVE_CHANGES,
                        ];

                        $instantMessengerClassesArray = new InstantMessengerDataClassesArray();
                        $instantMessengerClassesArray->append(WhatsappData::class);
                        $instantMessengerClassesArray->append(FacebookMessengerData::class);
                        $instantMessengerClassesArray->append(TelegramData::class);
                        $floatingButtonInstantMessengerTypeData = $instantMessengerService->getFloatingButtonInstantMessengerTypeData($instantMessengerClassesArray);

                        try {
                            echo $twig->render('@InstantMessengerIntegration/legacy-sitemgr-form-settings-instant-messenger-integration-panel.html.twig', ['instantMessengerIntegrationSettings' => $instantMessengerDomainSetting, 'floatingButtonPositionData' => $floatingButtonPositionData, 'floatingButtonInstantMessengerTypeData' => $floatingButtonInstantMessengerTypeData, 'saveButtonData' => $saveButtonData]);
                        } catch (Twig_Error_Loader $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Load error on template 'legacy-sitemgr-form-settings-instant-messenger-integration-panel.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Runtime $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Runtime error on template 'legacy-sitemgr-form-settings-instant-messenger-integration-panel.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Syntax $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Syntax error on template 'legacy-sitemgr-form-settings-instant-messenger-integration-panel.html.twig'.", ['exception' => $e]);
                                }
                            }
                        }
                        unset($instantMessengerIntegrationSettings,
                            $floatingButtonPositionData,
                            $floatingButtonInstantMessengerTypeData,
                            $saveButtonData);
                    }
                    unset($settings,
                        $twig);
                }
                unset($httpPostArray,
                    $httpGetArray);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getGeneralSettingsAfterRenderForm method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
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
                $pageId = $params['page_id'];
                $pageWidgets = $params['page_widgets'];
                if (isset($params['html_return']) && !empty($pageId)) {
                    $htmlReturnRef = &$params['html_return'];
                    $doctrine = $this->container->get('doctrine');
                    if (!empty($doctrine)) {
                        $pageRepository = $doctrine->getRepository('WysiwygBundle:PageWidget');
                        if (!empty($pageRepository)) {
                            /** @var PageWidget $pageWidget */
                            $pageWidget = $pageRepository->findOneBy([
                                'pageId' => $pageId,
                            ]);
                            if ($pageWidget !== null) {
                                $page = $pageWidget->getPage();
                                if ($page !== null) {
                                    $pageWidgetsPagePageType = $page->getPageType();
                                    if ($pageWidgetsPagePageType !== null) {
                                        $pageWidgetsPagePageTypeTitle = $pageWidgetsPagePageType->getTitle();
                                        if (!empty($pageWidgetsPagePageTypeTitle)) {
                                            if (!in_array($pageWidgetsPagePageTypeTitle, [
                                                PageType::RESULTS_PAGE,
                                                PageType::ARTICLE_DETAIL_PAGE,
                                                PageType::BLOG_DETAIL_PAGE,
                                                PageType::CLASSIFIED_DETAIL_PAGE,
                                                PageType::DEAL_DETAIL_PAGE,
                                                PageType::EVENT_DETAIL_PAGE,
                                                PageType::LISTING_DETAIL_PAGE,], true)) {
                                                $settings = $this->container->get('settings');
                                                /** @var Twig_Environment $twig */
                                                $twig = $this->container->get('twig');
                                                /** @var InstantMessengerService $instantMessengerService */
                                                $instantMessengerService = $this->container->get('instantmessenger.service');
                                                if (!empty($settings) && $twig !== null && $instantMessengerService !== null) {
                                                    $lang = 'en';
                                                    /**
                                                     * @var LanguageHandler $languageHandler
                                                     */
                                                    $languageHandler = $this->container->get('languagehandler');
                                                    if ($languageHandler !== null) {
                                                        $multiDomainInfo = $this->container->get('multi_domain.information');
                                                        if (!empty($multiDomainInfo)) {
                                                            $domainLocale = $multiDomainInfo->getLocale();
                                                            $lang = $languageHandler->getISOLang($domainLocale);
                                                            unset($domainLocale);
                                                        }
                                                        unset($multiDomainInfo);
                                                    }
                                                    unset($languageHandler);
                                                    $instantMessengerService->setLang($lang);

                                                    $instantMessengerFloatingButtonData = new InstantMessengerFloatingButtonData();
                                                    $instantMessengerDomainSetting = new InstantMessengerDomainSetting();
                                                    $encodedJsonInstantMessengerSiteManagerSettings = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_settings');
                                                    if (!empty($encodedJsonInstantMessengerSiteManagerSettings)) {
                                                        try {
                                                            $instantMessengerService->extractDomainSettingFromEncodedJson($encodedJsonInstantMessengerSiteManagerSettings, $instantMessengerDomainSetting);
                                                            if (!$instantMessengerService->isDomainSettingEmpty($instantMessengerDomainSetting)) {
                                                                if ($instantMessengerDomainSetting->displayFloatingButtonOption === 'on') {
                                                                    $instantMessengerFloatingButtonData->position = $instantMessengerDomainSetting->floatingButtonPosition;
                                                                    $instantMessengerFloatingButtonData->type = $instantMessengerDomainSetting->floatingButtonInstantMessengerType;
                                                                }
                                                            }
                                                        } catch (InstantMessengerServiceException $e) {
                                                            if ($logger!==null) {
                                                                $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                                            } else {
                                                                $notLoggedCriticalException = $e->getPrevious();
                                                            }
                                                        }
                                                    }
                                                    unset($encodedJsonInstantMessengerSiteManagerSettings);

                                                    if ($instantMessengerDomainSetting->displayFloatingButtonOption === 'on' &&
                                                        !empty($instantMessengerFloatingButtonData->position) &&
                                                        !empty($instantMessengerFloatingButtonData->type)) {
                                                        $instantMessengerDataArray = new InstantMessengerDataArray();
                                                        $instantMessengerWhatsappData = new WhatsappData();
                                                        $instantMessengerMessengerData = new FacebookMessengerData();
                                                        $instantMessengerTelegramData = new TelegramData();
                                                        $instantMessengerDataArray->append($instantMessengerWhatsappData);
                                                        $instantMessengerDataArray->append($instantMessengerMessengerData);
                                                        $instantMessengerDataArray->append($instantMessengerTelegramData);

                                                        $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                                                        if (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                                                            try {
                                                                $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                                                            } catch (InstantMessengerServiceException $e) {
                                                                if ($logger!==null) {
                                                                    $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                                                } else {
                                                                    $notLoggedCriticalException = $e->getPrevious();
                                                                }
                                                            }

                                                            $instantMessengerService->extractInstantMessengerButtonDataFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerFloatingButtonData);

                                                            if (!empty($instantMessengerFloatingButtonData->hRef)) {
                                                                try {
                                                                    $htmlReturnRef .= $twig->render('@InstantMessengerIntegration/instant-messenger-floating-button.html.twig', ['instantMessengerFloatingButtonData' => $instantMessengerFloatingButtonData]);
                                                                } catch (Twig_Error_Loader $e) {
                                                                    if ($this->devEnvironment) {
                                                                        echo '<div class="contact-item item-phone">Error on template load.</div>';
                                                                    } else {
                                                                        if ($logger!==null) {
                                                                            $logger->error("Load error on template 'instant-messenger-floating-button.html.twig'.", ['exception' => $e]);
                                                                        }
                                                                    }
                                                                } catch (Twig_Error_Runtime $e) {
                                                                    if ($this->devEnvironment) {
                                                                        echo '<div class="contact-item item-phone">Error on run template.</div>';
                                                                    } else {
                                                                        if ($logger!==null) {
                                                                            $logger->error("Runtime error on template 'instant-messenger-floating-button.html.twig'.", ['exception' => $e]);
                                                                        }
                                                                    }
                                                                } catch (Twig_Error_Syntax $e) {
                                                                    if ($this->devEnvironment) {
                                                                        echo '<div class="contact-item item-phone">Error on template syntax.</div>';
                                                                    } else {
                                                                        if ($logger!==null) {
                                                                            $logger->error("Syntax error on template 'instant-messenger-floating-button.html.twig'.", ['exception' => $e]);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        unset($encodedJsonInstantMessengerSiteManagerData);
                                                    }
                                                    unset($displayFloatingButton,
                                                        $instantMessengerFloatingButtonData);
                                                }
                                                unset($settings,
                                                    $twig);
                                            }
                                        }
                                        unset($pageWidgetsPagePageTypeTitle);
                                    }
                                    unset($pageWidgetsPagePageType);
                                }
                                unset($page);
                            }
                            unset($pageWidget);
                        }
                        unset($pageRepository);
                    }
                    unset($doctrine);
                }
                unset($pageWidgets);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getWysiwygExtensionRenderpageAfterRenderWidgets method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getSitemgrConfigurationBasicInformationAfterIncludeFormSiteinfo - Renders the InstantMessaging panel in the basic information page.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getSitemgrConfigurationBasicInformationAfterIncludeFormSiteinfo(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $httpPostArray = $params['http_post_array'];
                $httpGetArray = $params['http_get_array'];
                if (isset($httpPostArray, $httpGetArray) && is_array($httpPostArray) && is_array($httpGetArray)) {
                    /** @var Twig_Environment $twig */
                    $twig = $this->container->get('twig');
                    /** @var Settings $settings */
                    $settings = $this->container->get('settings');
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if ($settings !== null && $twig !== null && $instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            $sitemgrLocale = $settings->getSetting('sitemgr_language');
                            $lang = $languageHandler->getISOLang($sitemgrLocale);
                            unset($sitemgrLocale, $languageHandler);
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);

                        $instantMessengerDataArray = new InstantMessengerDataArray();
                        $instantMessengerWhatsappData = new WhatsappData();
                        $instantMessengerMessengerData = new FacebookMessengerData();
                        $instantMessengerTelegramData = new TelegramData();
                        $instantMessengerDataArray->append($instantMessengerWhatsappData);
                        $instantMessengerDataArray->append($instantMessengerMessengerData);
                        $instantMessengerDataArray->append($instantMessengerTelegramData);

                        $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');

                        if (!empty($httpPostArray) && array_key_exists('plugin', $httpPostArray)) {
                            $instantMessengerService->extractImDataFromHttpPostArray($httpPostArray, $instantMessengerDataArray);
                        } elseif (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                            try {
                                $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                            } catch (InstantMessengerServiceException $e) {
                                if ($logger!==null) {
                                    $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                } else {
                                    $notLoggedCriticalException = $e->getPrevious();
                                }
                            }
                        }
                        unset($encodedJsonInstantMessengerSiteManagerData);

                        try {
                            $footerData = (object)[
                                'renderSaveButton' => true,
                                'dataLoadingText' => LANG_LABEL_FORM_WAIT,
                                'onclick' => DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : 'document.header.submit();',
                                'saveButtonLabel' => LANG_SITEMGR_SAVE_CHANGES,
                            ];
                            echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel-with-footer.html.twig', ['messengerData' => $instantMessengerMessengerData, 'telegramData' => $instantMessengerTelegramData, 'whatsappData' => $instantMessengerWhatsappData, 'highlight' => null, 'footerData' => $footerData]);
                        } catch (Twig_Error_Loader $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Load error on template 'legacy-instant-messaging-panel-with-footer.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Runtime $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Runtime error on template 'legacy-instant-messaging-panel-with-footer.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Syntax $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Syntax error on template 'legacy-instant-messaging-panel-with-footer.html.twig'.", ['exception' => $e]);
                                }
                            }
                        }
                        unset($instantMessengerWhatsappData,
                            $instantMessengerMessengerData,
                            $instantMessengerTelegramData);
                    }
                    unset($settings,
                        $twig);
                }
                unset($httpPostArray,
                    $httpGetArray);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getSitemgrConfigurationBasicInformationAfterIncludeFormSiteinfo method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getSitemgrCodeContentBasicSettingsPostRequestHandleBeforeCheckSuccess - This method validates the plugin specific fields in site manager basic settings page before check success.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getSitemgrCodeContentBasicSettingsPostRequestHandleBeforeCheckSuccess(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $httpPostArray = $params['http_post_array'];
                $httpGetArray = $params['http_get_array'];
                if (isset($httpGetArray, $params['error_messages_array']) && !empty($httpPostArray) && is_array($httpPostArray) && is_array($httpGetArray) && is_array(['error_messages_array'])) {
                    //error_messages_array references to the form message errors array - where the validation errors need to be registered
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if ($instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            $settings = $this->container->get('settings');
                            if (!empty($settings)) {
                                $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                $lang = $languageHandler->getISOLang($sitemgrLocale);
                                unset($sitemgrLocale);
                            }
                            unset($settings);
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);
                        $instantMessengerClassesArray = new InstantMessengerDataClassesArray();
                        $instantMessengerClassesArray->append(WhatsappData::class);
                        $instantMessengerClassesArray->append(FacebookMessengerData::class);
                        $instantMessengerClassesArray->append(TelegramData::class);
                        $instantMessengerService->validateImDataFromHttpPostArray($httpPostArray, $params['error_messages_array'], $instantMessengerClassesArray);
                    }
                    unset($instantMessengerService);
                }
                unset($httpPostArray, $httpGetArray);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getSitemgrCodeContentBasicSettingsPostRequestHandleBeforeCheckSuccess method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getSitemgrCodeContentBasicSettingsPostRequestHandleAfterRegisterSuccess - This method deal with the site manager contact information domain settings related to the plugin after save all site manager contact information domain settings.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getSitemgrCodeContentBasicSettingsPostRequestHandleAfterRegisterSuccess(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $httpPostArray = $params['http_post_array'];
                $httpGetArray = $params['http_get_array'];
                if (isset($httpGetArray, $params['do_mixpanel_track'], $params['mixpanel_track_event_name'], $params['success_messages_array']) && !empty($httpPostArray) && is_array($httpPostArray) && is_array($httpGetArray) && is_array(['success_messages_array'])) {
                    //success_messages_array references to the form message success array - where the success messages need to be registered
                    $doMixpanelTrackRef = &$params['do_mixpanel_track'];
                    $params['mixpanel_track_event_name'] = 'Changed instant messenger information';
                    $settings = $this->container->get('settings');
                    if (!empty($settings)) {
                        /** @var InstantMessengerService $instantMessengerService */
                        $instantMessengerService = $this->container->get('instantmessenger.service');
                        if ($instantMessengerService !== null) {
                            $lang = 'en';
                            /**
                             * @var LanguageHandler $languageHandler
                             */
                            $languageHandler = $this->container->get('languagehandler');
                            if ($languageHandler !== null) {
                                $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                $lang = $languageHandler->getISOLang($sitemgrLocale);
                                unset($sitemgrLocale, $languageHandler);
                            }
                            unset($languageHandler);
                            $instantMessengerService->setLang($lang);

                            $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                            $instantMessengerDataArrayFromPostArray = new InstantMessengerDataArray();
                            $instantMessengerWhatsappData = new WhatsappData();
                            $instantMessengerMessengerData = new FacebookMessengerData();
                            $instantMessengerTelegramData = new TelegramData();
                            $instantMessengerDataArrayFromPostArray->append($instantMessengerWhatsappData);
                            $instantMessengerDataArrayFromPostArray->append($instantMessengerMessengerData);
                            $instantMessengerDataArrayFromPostArray->append($instantMessengerTelegramData);
                            $instantMessengerService->extractImDataFromHttpPostArray($httpPostArray, $instantMessengerDataArrayFromPostArray);

                            try {
                                if (empty($encodedJsonInstantMessengerSiteManagerData) || $instantMessengerService->hasImDataDifferenceBetweenInstantMessengerDataArrayAndEncodedJsonString($instantMessengerDataArrayFromPostArray, $encodedJsonInstantMessengerSiteManagerData)) {
                                    $doMixpanelTrackRef = true;
                                    $encodedJsonInstantMessengerValue = json_encode($instantMessengerDataArrayFromPostArray->convertToArrayToBeJsonEncoded());
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        if (!empty($encodedJsonInstantMessengerValue)) {
                                            $settings->setSetting('plugin_instant_messenger_integration_site_manager_data', $encodedJsonInstantMessengerValue);
                                        }
                                    } else {
                                        $e = new Exception(json_last_error_msg(), json_last_error());
                                        if ($logger !== null) {
                                            $logger->critical('Unexpected json error on getSitemgrCodeContentBasicSettingsPostRequestHandleAfterRegisterSuccess method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                                        } else {
                                            $notLoggedCriticalException = $e;
                                        }
                                    }
                                    unset($unencodedJsonInstantMessengerValue,
                                        $encodedJsonInstantMessengerValue);
                                }
                            } catch (InstantMessengerServiceException $e) {
                                if ($logger !== null) {
                                    $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                } else {
                                    $notLoggedCriticalException = $e->getPrevious();
                                }
                            }

                            unset($encodedJsonInstantMessengerSiteManagerData);
                        }
                        unset($instantMessengerService);
                    }
                    unset($settings);
                }
                unset($httpPostArray,
                    $httpGetArray);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getSitemgrCodeContentBasicSettingsPostRequestHandleAfterRegisterSuccess method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getClassListingLevelConstruct - This method will inject the instant_messenger_integration attribute in the ListingLevel object.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getClassListingLevelConstruct(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $that = $params['that'];
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($that)) {
                    $thatRef = &$params['that'];
                    if (!empty($that->value)) {
                        $doctrine = $this->container->get('doctrine');
                        if (!empty($doctrine)) {
                            $em = $doctrine->getManager();
                            if (!empty($em)) {
                                $listingLevelInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                                if (!empty($listingLevelInstantMessengerRepository)) {
                                    foreach ($thatRef->value as $levelIndex => $levelValue) {
                                        $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy(['level' => $levelValue]);
                                        if (!empty($listingLevelInstantMessenger)) {
                                            $thatRef->instant_messenger_integration[$levelIndex] = $listingLevelInstantMessenger->getInstantMessenger();
                                        }
                                        unset($listingLevelInstantMessenger);
                                    }
                                }
                                unset($listingLevelInstantMessengerRepository);
                            }
                            unset($em);
                        }
                        unset($doctrine);
                    }
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getClassListingLevelConstruct method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getFormPricingAfterAddFields - This method will insert a new levelOption entry that will be user to render the fields related to the InstantMessenger in the manage levels and pricing.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getFormPricingAfterAddFields(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $type = $params['type'];
            $levelOptions = $params['levelOptions'];
            $levelFields = $params['levelFields'];
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($type) && !empty($levelOptions) && !empty($levelFields)) {
                    $levelOptionsRef = &$params['levelOptions'];
                    if ('listing' === $type) {
                        $translator = $this->container->get('translator');
                        if (!empty($translator)) {
                            $levelOptionsRef[] = [
                                'name' => 'instant_messenger_integration',
                                'type' => 'checkbox',
                                'title' => $translator->trans('Enable instant Messenger integration'),
                                'tip' => $translator->trans('Allow owners to use the Instant Messenger integration?'),
                            ];
                        }
                        unset($translator);
                    }
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getFormPricingAfterAddFields method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $levelFields,
                    $levelOptions,
                    $type);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getPaymentGatewayAfterSaveListingLevel - This method will save the InstantMessenger data related with the ListingLevel, edited in the manage levels and pricing.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getPaymentGatewayAfterSaveListingLevel(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $levelOptionData = $params['levelOptionData'];
            $levelValue = $params['levelValue'];
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($levelOptionData) && !empty($levelValue)) {
                    $doctrine = $this->container->get('doctrine');
                    if (!empty($doctrine)) {
                        $em = $doctrine->getManager();
                        if (!empty($em)) {
                            $listingLevelInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                            if (!empty($listingLevelInstantMessengerRepository)) {
                                $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy(['level' => $levelValue]);
                                if (!empty($listingLevelInstantMessenger)) {
                                    $levelInstantMessengerValueArray = $levelOptionData['instant_messenger_integration'];
                                    if (empty($levelInstantMessengerValueArray)) {
                                        $listingLevelInstantMessenger->setInstantMessenger('n');
                                    } else {
                                        if (array_key_exists($levelValue, $levelInstantMessengerValueArray)) {
                                            $listingLevelInstantMessenger->setInstantMessenger('y');
                                        } else {
                                            $listingLevelInstantMessenger->setInstantMessenger('n');
                                        }
                                    }
                                    $em->persist($listingLevelInstantMessenger);
                                    $em->flush();
                                    unset($levelInstantMessengerValueArray);
                                }
                                unset($listingLevelInstantMessenger);
                            }
                            unset($listingLevelInstantMessengerRepository);
                        }
                        unset($em);
                    }
                    unset($doctrine);
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getPaymentGatewayAfterSaveListingLevel method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $levelValue,
                    $levelOptionData);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * Insert the plugin related field names inside of array obtained by getListingLevelFieldsNameByLevel call on getListingLevelFieldsAction of ListingBundle DefaultController
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getListingBundleControllerGetlistingLevelFieldsActionAfterGetListingLevelFieldsNameByLevel(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $fieldsArrayRef = &$params['fields_array'];
            $levelFieldsArrayRef = &$params['level_fields_array'];
            $levelValue = $params['level_value_from_get'];
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (is_array($fieldsArrayRef) && !empty($levelValue) && is_array($levelFieldsArrayRef)) {
                    if(!in_array('instant_messenger_integration',$fieldsArrayRef,true)){
                        $fieldsArrayRef[] = 'instant_messenger_integration';
                    }
                    $doctrine = $this->container->get('doctrine');
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if (!empty($doctrine) && $instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            if (!$this->isSitemgr()) {
                                $multiDomainInfo = $this->container->get('multi_domain.information');
                                if (!empty($multiDomainInfo)) {
                                    $domainLocale = $multiDomainInfo->getLocale();
                                    $lang = $languageHandler->getISOLang($domainLocale);
                                    unset($domainLocale);
                                }
                                unset($multiDomainInfo);
                            } else {
                                $settings = $this->container->get('settings');
                                if (!empty($settings)) {
                                    $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                    $lang = $languageHandler->getISOLang($sitemgrLocale);
                                    unset($sitemgrLocale, $languageHandler);
                                }
                                unset($settings);
                            }
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);
                        $listingLevelInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                        if (!empty($listingLevelInstantMessengerRepository)) {
                            if (!empty($levelValue)) {
                                $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy(['level' => $levelValue]);
                                if (!empty($listingLevelInstantMessenger)) {
                                    $instantMessengerEnabled = $listingLevelInstantMessenger->getInstantMessenger();
                                    if ('y' === $instantMessengerEnabled) {
                                        if(!in_array('instant_messenger_integration',$levelFieldsArrayRef,true)){
                                            $levelFieldsArrayRef[] = array('field' => 'instant_messenger_integration');
                                        }
                                    }
                                    unset($instantMessengerEnabled);
                                }
                                unset($listingLevelInstantMessenger);
                            }
                        }
                        unset($listingLevelInstantMessengerRepository);
                    }
                    unset($instantMessengerService, $doctrine);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingBundleControllerGetlistingLevelFieldsActionAfterGetListingLevelFieldsNameByLevel method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $levelValue);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * Insert the plugin related fields inside of listing form after contact information panel.
     *
     * @param null $params
     *
     * @param bool $isSponsorPage
     * @throws Exception
     */
    private function getListingFormAfterContactInformationPanel(&$params = null, $isSponsorPage = false): void
    {
        if (!empty($params) && !empty($this->container)) {
            $listing = $params['listing'];
            $levelValue = $params['level'];
            $highlight = $params['highlight'];
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($listing)) {
                    $translator = $this->container->get('translator');
                    $doctrine = $this->container->get('doctrine');
                    /** @var Twig_Environment $twig */
                    $twig = $this->container->get('twig');
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if (!empty($translator) && !empty($doctrine) && $twig !== null && $instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            if ($isSponsorPage) {
                                $multiDomainInfo = $this->container->get('multi_domain.information');
                                if (!empty($multiDomainInfo)) {
                                    $domainLocale = $multiDomainInfo->getLocale();
                                    $lang = $languageHandler->getISOLang($domainLocale);
                                    unset($domainLocale);
                                }
                                unset($multiDomainInfo);
                            } else {
                                $settings = $this->container->get('settings');
                                if (!empty($settings)) {
                                    $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                    $lang = $languageHandler->getISOLang($sitemgrLocale);
                                    unset($sitemgrLocale, $languageHandler);
                                }
                                unset($settings);
                            }
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);
                        $em = $doctrine->getManager();
                        if (!empty($em)) {
                            /** @var ListingLevelInstantMessengerRepository $listingLevelInstantMessengerRepository */
                            $listingLevelInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                            $listingInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingInstantMessenger');
                            if ($listingLevelInstantMessengerRepository !== null && !empty($listingInstantMessengerRepository)) {
                                if (!empty($levelValue)) {

                                    $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy(['level' => $levelValue]);
                                    if (!empty($listingLevelInstantMessenger)) {
                                        $instantMessengerEnabled = $listingLevelInstantMessenger->getInstantMessenger();
                                        $blockPanel = $instantMessengerEnabled !== 'y';
                                        $instantMessengerWhatsappData = new WhatsappData();
                                        $instantMessengerMessengerData = new FacebookMessengerData();
                                        $instantMessengerTelegramData = new TelegramData();
                                        $instantMessengerDataArray = new InstantMessengerDataArray();
                                        $instantMessengerDataArray->append($instantMessengerWhatsappData);
                                        $instantMessengerDataArray->append($instantMessengerMessengerData);
                                        $instantMessengerDataArray->append($instantMessengerTelegramData);

                                        //If the listing has the instant_messenger_integration property, it is came from a form response. In this case, use the values come from the form.
                                        if (property_exists($listing, 'instant_messenger_integration') && !empty($listing->instant_messenger_integration)) {
                                            $instantMessengerService->extractImDataFromInstantMessengerInjectedLegacyClassListing($listing->instant_messenger_integration, $instantMessengerDataArray);
                                        } elseif (!empty($listing->id)) {
                                            try {
                                                $listingInstantMessenger = null;
                                                $instantMessengerService->extractImDataFromDatabaseListing($listing->id, $instantMessengerDataArray, $listingInstantMessenger);
                                                unset($listingInstantMessenger);
                                            } catch (InstantMessengerServiceException $e) {
                                                if ($logger !== null) {
                                                    $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                                } else {
                                                    $notLoggedCriticalException = $e->getPrevious();
                                                }
                                            }
                                        }

                                        $locale = substr($lang, 0, 2);
                                        $levelsString = '';
                                        $listingLevelsNameArray = $listingLevelInstantMessengerRepository->getEnabledListingLevelsNameArray();
                                        foreach($listingLevelsNameArray as $key => $listingLevelsNameArrayValue) {
                                            if(empty($levelsString)) {
                                                $levelsString .= $listingLevelsNameArrayValue['name'];
                                                continue;
                                            }
                                            end($listingLevelsNameArray);
                                            if($key === key($listingLevelsNameArray)) {
                                                $levelsString .= ' ' . $translator->trans('and', [], 'messages', $locale) . ' ' . $listingLevelsNameArrayValue['name'];
                                            } else {
                                                $levelsString .= ', ' . $listingLevelsNameArrayValue['name'];
                                            }
                                        }
                                        $blockFieldListingLevelText = $translator->trans('Content not available', [], 'administrator', $locale);
                                        if ($isSponsorPage) {
                                            $blockFieldListingLevelText = $translator->trans('%start_upgrade% Upgrade your plan %end_upgrade% and get access to this content', ['%start_upgrade%' => '<a data-toggle="modal" href="#modal-upgrade" class="link">', '%end_upgrade%' => '</a>'], 'administrator', $locale);
                                        } elseif (!empty($levelsString)) {
                                            $blockFieldListingLevelText = $translator->transChoice('Content available only for %levels% levels.', count($listingLevelsNameArray), ['%levels%' => $levelsString], 'administrator', $locale);
                                        }

                                        try {
                                            echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel.html.twig', ['messengerData' => $instantMessengerMessengerData, 'telegramData' => $instantMessengerTelegramData, 'whatsappData' => $instantMessengerWhatsappData, 'highlight' => $highlight, 'blockPanel' => $blockPanel, 'blockFieldListingLevelText' => $blockFieldListingLevelText, 'panelType' => 'form']);
                                        } catch (Twig_Error_Loader $e) {
                                            if ($this->devEnvironment) {
                                                echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                                            } else {
                                                if ($logger !== null) {
                                                    $logger->error("Load error on template 'legacy-instant-messaging-panel.html.twig'.", ['exception' => $e]);
                                                }
                                            }
                                        } catch (Twig_Error_Runtime $e) {
                                            if ($this->devEnvironment) {
                                                echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                                            } else {
                                                if ($logger !== null) {
                                                    $logger->error("Runtime error on template 'legacy-instant-messaging-panel.html.twig'.", ['exception' => $e]);
                                                }
                                            }
                                        } catch (Twig_Error_Syntax $e) {
                                            if ($this->devEnvironment) {
                                                echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                                            } else {
                                                if ($logger !== null) {
                                                    $logger->error("Syntax error on template 'legacy-instant-messaging-panel.html.twig'.", ['exception' => $e]);
                                                }
                                            }
                                        }
                                        unset($lang,
                                            $instantMessengerWhatsappData,
                                            $instantMessengerMessengerData,
                                            $instantMessengerTelegramData,
                                            $instantMessengerEnabled,
                                            $instantMessengerDataArray,
                                            $blockPanel);

                                    }
                                    unset($listingLevelInstantMessenger);
                                }
                            }
                            unset($listingLevelInstantMessengerRepository,
                                $listingInstantMessengerRepository);
                        }
                        unset($em);
                    }
                    unset($doctrine,
                        $translator,
                        $twig);
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getListingFormAfterContactInformationPanel method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $listing,
                    $levelValue,
                    $highlight);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getValidateFunctValidateListing - This method validates the plugin specific fields in the listing data before save.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getValidateFunctValidateListing(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $array = $params['array'];
                if (!empty($array) && is_array($array) && isset($params['errors']) && is_array($params['errors'])) {
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if ($instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            $settings = $this->container->get('settings');
                            if (!empty($settings)) {
                                $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                $lang = $languageHandler->getISOLang($sitemgrLocale);
                                unset($sitemgrLocale);
                            }
                            unset($settings);
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);
                        $instantMessengerClassesArray = new InstantMessengerDataClassesArray();
                        $instantMessengerClassesArray->append(WhatsappData::class);
                        $instantMessengerClassesArray->append(FacebookMessengerData::class);
                        $instantMessengerClassesArray->append(TelegramData::class);
                        $instantMessengerService->validateImDataFromHttpPostArray($array, $params['errors'], $instantMessengerClassesArray);
                    }
                    unset($instantMessengerService);
                }
                unset($array);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getValidateFunctValidateListing method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * Inject specific plugin form data into listing object when the form data was send no matter if was editing or inserting a new one.
     *
     * @param null $params
     * @throws Exception
     */
    private function getListingCodeAfterSetupForm(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $httpPostArray = $params['http_post_array'];
                if (!empty($params['listing']) && !empty($httpPostArray) && is_array($httpPostArray)) {
                    $listingRef = &$params['listing'];
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if ($instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            $settings = $this->container->get('settings');
                            if (!empty($settings)) {
                                $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                $lang = $languageHandler->getISOLang($sitemgrLocale);
                                unset($sitemgrLocale);
                            }
                            unset($settings);
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);
                        $instantMessengerDataArray = new InstantMessengerDataArray();
                        $instantMessengerWhatsappData = new WhatsappData();
                        $instantMessengerMessengerData = new FacebookMessengerData();
                        $instantMessengerTelegramData = new TelegramData();
                        $instantMessengerDataArray->append($instantMessengerWhatsappData);
                        $instantMessengerDataArray->append($instantMessengerMessengerData);
                        $instantMessengerDataArray->append($instantMessengerTelegramData);
                        $instantMessengerService->extractImDataFromHttpPostArray($httpPostArray, $instantMessengerDataArray);
                        $addInstantMessengerIntegrationDataOnListing = !$instantMessengerDataArray->isAllItemsWithEmptyProperties();

                        $instantMessengerIntegrationData = $instantMessengerDataArray->convertToListingValuesObject();

                        if ($addInstantMessengerIntegrationDataOnListing && $instantMessengerIntegrationData !== null) {
                            $listingRef->instant_messenger_integration = $instantMessengerIntegrationData;
                        } else {
                            $listingRef->instant_messenger_integration = null;
                        }
                    }
                }
                unset($httpPostArray);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getListingCodeAfterSetupForm method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $that);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * Save specific plugin data related to the listing after the listing has been saved.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getListingCodeAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $listing = $params['listing'];
                $level = $params['level'];
                $http_post_array = $params['http_post_array'];
                if (!empty($http_post_array) && is_array($http_post_array)) {
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if ($instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            if (!$this->isSitemgr()) {
                                $multiDomainInfo = $this->container->get('multi_domain.information');
                                if (!empty($multiDomainInfo)) {
                                    $domainLocale = $multiDomainInfo->getLocale();
                                    $lang = $languageHandler->getISOLang($domainLocale);
                                    unset($domainLocale);
                                }
                                unset($multiDomainInfo);
                            } else {
                                $settings = $this->container->get('settings');
                                if (!empty($settings)) {
                                    $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                    $lang = $languageHandler->getISOLang($sitemgrLocale);
                                    unset($sitemgrLocale, $languageHandler);
                                }
                                unset($settings);
                            }
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);

                        if (!empty($listing) && !empty($listing->level) && !empty($listing->id)) {
                            $doctrine = $this->container->get('doctrine');
                            if (!empty($doctrine)) {
                                $em = $doctrine->getManager();
                                if (!empty($em)) {
                                    $listingLevelInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                                    $listingRepository = $doctrine->getRepository('ListingBundle:Listing');
                                    if (!empty($listingLevelInstantMessengerRepository) /*&& !empty($listingInstantMessengerRepository)*/ && !empty($listingRepository)) {
                                        $listingEntity = $listingRepository->findOneBy(['id' => $listing->id]);
                                        if (!empty($listingEntity)) {
                                            $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy(['level' => $listing->level]);
                                            if (!empty($listingLevelInstantMessenger)) {
                                                $instantMessengerEnabled = $listingLevelInstantMessenger->getInstantMessenger();
                                                if ('y' === $instantMessengerEnabled) {
                                                    $instantMessengerDataArray = new InstantMessengerDataArray();
                                                    $instantMessengerWhatsappData = new WhatsappData();
                                                    $instantMessengerMessengerData = new FacebookMessengerData();
                                                    $instantMessengerTelegramData = new TelegramData();
                                                    $instantMessengerDataArray->append($instantMessengerWhatsappData);
                                                    $instantMessengerDataArray->append($instantMessengerMessengerData);
                                                    $instantMessengerDataArray->append($instantMessengerTelegramData);
                                                    $listingInstantMessenger = null;
                                                    try {
                                                        $instantMessengerService->extractImDataFromDatabaseListing($listing->id, $instantMessengerDataArray, $listingInstantMessenger);
                                                    } catch (InstantMessengerServiceException $e) {
                                                        if ($logger!==null) {
                                                            $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                                        } else {
                                                            $notLoggedCriticalException = $e->getPrevious();
                                                        }
                                                    }

                                                    if (empty($listingInstantMessenger)) {
                                                        $listingInstantMessenger = new ListingInstantMessenger();
                                                        $listingInstantMessenger->setListing($listingEntity);
                                                    }

                                                    $instantMessengerService->extractImDataFromHttpPostArray($http_post_array, $instantMessengerDataArray);

                                                    $unencodedJsonInstantMessengerValue = $instantMessengerDataArray->convertToArrayToBeJsonEncoded();
                                                    $encodedJsonInstantMessengerValue = json_encode($unencodedJsonInstantMessengerValue);
                                                    if (!empty($encodedJsonInstantMessengerValue) && JSON_ERROR_NONE === json_last_error()) {
                                                        $listingInstantMessenger->setInstantMessenger($encodedJsonInstantMessengerValue);
                                                        $em->persist($listingInstantMessenger);
                                                        $em->flush();
                                                    } else {
                                                        $e = new Exception(json_last_error_msg(), json_last_error());
                                                        if ($logger!==null) {
                                                            $logger->critical('Unexpected json error on getListingCodeAfterSave method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                                                        } else {
                                                            $notLoggedCriticalException = $e;
                                                        }
                                                    }

                                                    unset($unencodedJsonInstantMessengerValue,
                                                        $encodedJsonInstantMessengerValue,
                                                        $listingInstantMessenger,
                                                        $instantMessengerDataArray,
                                                        $instantMessengerWhatsappData,
                                                        $instantMessengerMessengerData,
                                                        $instantMessengerTelegramData);
                                                }
                                                unset($instantMessengerEnabled);
                                            }
                                            unset($listingLevelInstantMessenger);
                                        }
                                        unset($listingEntity);
                                    }
                                    unset($listingLevelInstantMessengerRepository,
                                        $listingRepository);
                                }
                                unset($em);
                            }
                            unset($doctrine);
                        }
                    }
                    unset($instantMessengerService);
                }
                unset($listing,
                    $level,
                    $http_post_array);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getListingCodeAfterSave method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $listing,
                    $level,
                    $http_post_array);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getClassListingBeforeDelete - Remove the plugin related listing registry from the database when the listing will be deleted.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getClassListingBeforeDelete(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $that = $params['that'];
                $doctrine = $this->container->get('doctrine');
                if (!empty($doctrine)) {
                    $em = $doctrine->getManager();
                    if (!empty($em)) {
                        $listingInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingInstantMessenger');
                        if (!empty($listingInstantMessengerRepository)) {
                            $listingInstantMessenger = $listingInstantMessengerRepository->findOneBy(['listing' => $that->id]);
                            if (!empty($listingInstantMessenger)) {
                                $em->remove($listingInstantMessenger);
                                $em->flush();
                            }
                            unset($listingInstantMessenger);
                        }
                        unset($listingInstantMessengerRepository);
                    }
                    unset($em);
                }
                unset($doctrine);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getClassListingBeforeDelete method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $listing,
                    $contentCount,
                    $overviewCount);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getSystemFunctAfterSetupGamefyItemsFields - Determine by the listing level related structure if the fields related with the plugin will be considered on additional fields check.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getSystemFunctAfterSetupGamefyItemsFields(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $notLoggedCriticalException = null;
            try {
                $itemType = $params['item_type'];
                $itemObj = $params['itemObj'];
                $arrayAdditional = $params['arrayAdditional'];
                $arrayAdditionalRef = &$params['arrayAdditional'];
                $arrayDescription = $params['arrayDescription'];
                $arrayMedia = $params['arrayMedia'];
                $arrayFields = $params['array_fields'];
                /** @var Logger $logger */
                $logger = $this->container->get('logger');
                if (!empty($itemObj) && !empty($itemType) && 'listing' === $itemType && !empty($itemObj->level) && !is_null($arrayAdditional) && is_array($arrayAdditional)) {
                    $doctrine = $this->container->get('doctrine');
                    if (!empty($doctrine)) {
                        $em = $doctrine->getManager();
                        if (!empty($em)) {
                            $listingLevelInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingLevelInstantMessenger');
                            if (!empty($listingLevelInstantMessengerRepository)) {
                                $listingLevelInstantMessenger = $listingLevelInstantMessengerRepository->findOneBy(['level' => $itemObj->level]);
                                if (!empty($listingLevelInstantMessenger)) {
                                    $instantMessengerEnabled = $listingLevelInstantMessenger->getInstantMessenger();
                                    if ('y' === $instantMessengerEnabled) {
                                        $arrayAdditionalRef[] = 'instant_messenger_integration';
                                    }
                                }
                            }
                            unset($listingLevelInstantMessengerRepository);
                        }
                        unset($em);
                    }
                    unset($doctrine);
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getSystemFunctAfterSetupGamefyItemsFields method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $itemType,
                    $itemObj,
                    $arrayAdditional,
                    $arrayDescription,
                    $arrayMedia,
                    $arrayFields);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getSystemFunctAfterSetupGamefyItemsActivated - Check if the listing will consider the fields related with the plugin as marked as filled to the additional fields fill check.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getSystemFunctAfterSetupGamefyItemsActivated(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $itemType = $params['item_type'];
                $itemObj = $params['itemObj'];
                $arrayAdditional = $params['arrayAdditional'];
                $additionalFilledRef = &$params['additionalFilled'];
                if (!empty($itemObj) && !empty($itemType) && 'listing' === $itemType && !empty($itemObj->level) && !empty($itemObj->id) && $arrayAdditional !== null && is_array($arrayAdditional) && $additionalFilledRef !== null && is_array($additionalFilledRef)) {
                    if (in_array('instant_messenger_integration', $arrayAdditional, true)) {
                        $considerInstantMessengerIntegrationFilled = false;
                        /** @var InstantMessengerService $instantMessengerService */
                        $instantMessengerService = $this->container->get('instantmessenger.service');
                        if ($instantMessengerService !== null) {
                            $lang = 'en';
                            /**
                             * @var LanguageHandler $languageHandler
                             */
                            $languageHandler = $this->container->get('languagehandler');
                            if ($languageHandler !== null) {
                                if (!$this->isSitemgr()) {
                                    $multiDomainInfo = $this->container->get('multi_domain.information');
                                    if (!empty($multiDomainInfo)) {
                                        $domainLocale = $multiDomainInfo->getLocale();
                                        $lang = $languageHandler->getISOLang($domainLocale);
                                        unset($domainLocale);
                                    }
                                    unset($multiDomainInfo);
                                } else {
                                    $settings = $this->container->get('settings');
                                    if (!empty($settings)) {
                                        $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                        $lang = $languageHandler->getISOLang($sitemgrLocale);
                                        unset($sitemgrLocale, $languageHandler);
                                    }
                                    unset($settings);
                                }
                            }
                            unset($languageHandler);
                            $instantMessengerService->setLang($lang);

                            $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                            try {
                                /** @var ListingInstantMessenger $listingInstantMessenger */
                                $listingInstantMessenger = null;
                                $instantMessengerService->extractImDataFromDatabaseListing($itemObj->id, $instantMessengerDataArray, $listingInstantMessenger);
                                unset($listingInstantMessenger);
                            } catch (InstantMessengerServiceException $e) {
                                if ($logger!==null) {
                                    $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                } else {
                                    $notLoggedCriticalException = $e->getPrevious();
                                }
                            }
                            $considerInstantMessengerIntegrationFilled = !$instantMessengerService->isImDataEmpty($instantMessengerDataArray);
                            unset($instantMessengerDataArray);
                        }
                        unset($instantMessengerService);
                        if ($considerInstantMessengerIntegrationFilled) {
                            if (!in_array('instant_messenger_integration', $additionalFilledRef, true)) {
                                $additionalFilledRef[] = 'instant_messenger_integration';
                            }
                        }
                        unset($considerInstantMessengerIntegrationFilled);
                    }
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getSystemFunctAfterSetupGamefyItemsActivated method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $itemType,
                    $itemObj,
                    $arrayAdditional);

                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getListingDetailBeforeAddress - Renders the instant messenger links into the listing details, before rendering the Address block.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getListingDetailBeforeAddress(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $level = $params['level'];
                if (!empty($item) && !empty($level) && $item instanceof Listing) {
                    /** @var Twig_Environment $twig */
                    $twig = $this->container->get('twig');
                    /** @var InstantMessengerService $instantMessengerService */
                    $instantMessengerService = $this->container->get('instantmessenger.service');
                    if ($twig !== null && $instantMessengerService !== null) {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            $multiDomainInfo = $this->container->get('multi_domain.information');
                            if (!empty($multiDomainInfo)) {
                                $domainLocale = $multiDomainInfo->getLocale();
                                $lang = $languageHandler->getISOLang($domainLocale);
                                unset($domainLocale);
                            }
                            unset($multiDomainInfo);
                        }
                        unset($languageHandler);
                        $instantMessengerService->setLang($lang);
                        if ($level->hasInstantMessenger) {
                            $listingId = $item->getId();
                            if (!empty($listingId)) {
                                $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                try {
                                    $listingInstantMessenger = null;
                                    $instantMessengerService->extractImDataFromDatabaseListing($listingId, $instantMessengerDataArray, $listingInstantMessenger);
                                    unset($listingInstantMessenger);
                                } catch (InstantMessengerServiceException $e) {
                                    if ($logger!==null) {
                                        $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                    } else {
                                        $notLoggedCriticalException = $e->getPrevious();
                                    }
                                }
                                if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                    $instantMessengerLinkButtonDataArray = new InstantMessengerLinkButtonDataArray();
                                    if ($instantMessengerService->extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerLinkButtonDataArray)) {
                                        if ($instantMessengerLinkButtonDataArray->count() > 0) {
                                            try {
                                                echo $twig->render('@InstantMessengerIntegration/listing-detail-instant-messenger-integration-links.html.twig', ['instantMessengerDataArray' => $instantMessengerLinkButtonDataArray]);
                                            } catch (Twig_Error_Loader $e) {
                                                if ($this->devEnvironment) {
                                                    echo '<div class="contact-item item-phone">Error on template load.</div>';
                                                } else {
                                                    if ($logger!==null) {
                                                        $logger->error("Load error on template 'listing-detail-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                                    }
                                                }
                                            } catch (Twig_Error_Runtime $e) {
                                                if ($this->devEnvironment) {
                                                    echo '<div class="contact-item item-phone">Error on run template.</div>';
                                                } else {
                                                    if ($logger!==null) {
                                                        $logger->error("Runtime error on template 'listing-detail-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                                    }
                                                }
                                            } catch (Twig_Error_Syntax $e) {
                                                if ($this->devEnvironment) {
                                                    echo '<div class="contact-item item-phone">Error on template syntax.</div>';
                                                } else {
                                                    if ($logger!==null) {
                                                        $logger->error("Syntax error on template 'listing-detail-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    unset($instantMessengerLinkButtonDataArray);
                                }
                                unset($instantMessengerDataArray);
                            }
                            unset($listingId);
                        }
                    }
                    unset($twig, $instantMessengerService);
                }
                unset($item,
                    $level);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getListingDetailBeforeAddress method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getListingSummaryAfterAdditionalPhone - Renders the instant messenger links into the listing summary items, after the additional phone be processed/rendered.
     *
     * @param null $params array (includes the following keys: search_item, level, result_data)
     *
     * @throws Exception
     */
    private function getListingSummaryAfterAdditionalPhone(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $searchItem = $params['search_item'];
                $level = $params['level'];
                if (!empty($searchItem) && !empty($level) && method_exists($searchItem, 'getType') && method_exists($searchItem, 'getId')) {
                    $searchItemType = $searchItem->getType();
                    $searchItemId = $searchItem->getId();
                    if (!empty($searchItemType) && !empty($searchItemId) && 'listing' === $searchItemType) {
                        /** @var Twig_Environment $twig */
                        $twig = $this->container->get('twig');
                        $doctrine = $this->container->get('doctrine');
                        /** @var InstantMessengerService $instantMessengerService */
                        $instantMessengerService = $this->container->get('instantmessenger.service');
                        if ($twig !== null && !empty($doctrine) && $instantMessengerService !== null) {
                            $lang = 'en';
                            /**
                             * @var LanguageHandler $languageHandler
                             */
                            $languageHandler = $this->container->get('languagehandler');
                            if ($languageHandler !== null) {
                                $multiDomainInfo = $this->container->get('multi_domain.information');
                                if (!empty($multiDomainInfo)) {
                                    $domainLocale = $multiDomainInfo->getLocale();
                                    $lang = $languageHandler->getISOLang($domainLocale);
                                    unset($domainLocale);
                                }
                                unset($multiDomainInfo);
                            }
                            unset($languageHandler);
                            $instantMessengerService->setLang($lang);
                            if ($level->hasInstantMessenger) {
                                $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                try {
                                    $listingInstantMessenger = null;
                                    $instantMessengerService->extractImDataFromDatabaseListing($searchItemId, $instantMessengerDataArray, $listingInstantMessenger);
                                    unset($listingInstantMessenger);
                                } catch (InstantMessengerServiceException $e) {
                                    if ($logger!==null) {
                                        $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                    } else {
                                        $notLoggedCriticalException = $e->getPrevious();
                                    }
                                }
                                if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                    $instantMessengerLinkButtonDataArray = new InstantMessengerLinkButtonDataArray();
                                    if ($instantMessengerService->extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerLinkButtonDataArray)) {
                                        if ($instantMessengerLinkButtonDataArray->count() > 0) {
                                            try {
                                                echo $twig->render('@InstantMessengerIntegration/listing-summary-instant-messenger-integration-links.html.twig', ['instantMessengerDataArray' => $instantMessengerLinkButtonDataArray]);
                                            } catch (Twig_Error_Loader $e) {
                                                if ($this->devEnvironment) {
                                                    echo '<div class="contact-item item-phone">Error on template load.</div>';
                                                } else {
                                                    if ($logger!==null) {
                                                        $logger->error("Load error on template 'listing-summary-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                                    }
                                                }
                                            } catch (Twig_Error_Runtime $e) {
                                                if ($this->devEnvironment) {
                                                    echo '<div class="contact-item item-phone">Error on run template.</div>';
                                                } else {
                                                    if ($logger!==null) {
                                                        $logger->error("Runtime error on template 'listing-summary-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                                    }
                                                }
                                            } catch (Twig_Error_Syntax $e) {
                                                if ($this->devEnvironment) {
                                                    echo '<div class="contact-item item-phone">Error on template syntax.</div>';
                                                } else {
                                                    if ($logger!==null) {
                                                        $logger->error("Syntax error on template 'listing-summary-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    unset($instantMessengerLinkButtonDataArray);
                                }
                            }
                        }
                        unset($doctrine, $twig, $instantMessengerService);
                    }
                    unset($searchItemType,
                        $searchItemId);
                }
                unset($item,
                    $level);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getListingSummaryAfterAdditionalPhone method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getFormDesignSettingsAfterRenderFooterSpecificBlock - Method responsible to render the plugin related fields in the footer widget configuration.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getFormDesignSettingsAfterRenderFooterSpecificBlock(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $originalWidget = $params['originalWidget'];
                $pageWidget = $params['pageWidget'];
                if (!empty($originalWidget) && !empty($pageWidget) && is_object($originalWidget) && method_exists($originalWidget, 'getType') && method_exists($originalWidget, 'getTitle')) {
                    $widgetType = $originalWidget->getType();
                    $widgetTitle = $originalWidget->getTitle();
                    if (Widget::FOOTER_TYPE === $widgetType && in_array($widgetTitle, array(Widget::FOOTER, Widget::FOOTER_WITH_LOGO, Widget::FOOTER_WITH_SOCIAL_MEDIA, Widget::FOOTER_WITH_NEWSLETTER), true)) {
                        $widgetContent = $params['content'];
                        if (!empty($widgetContent) && is_array($widgetContent) && array_key_exists('pluginInstantMessengerIntegrationWidgetSettings', $widgetContent)) {
                            $pluginInstantMessengerIntegrationWidgetSettings = $widgetContent['pluginInstantMessengerIntegrationWidgetSettings'];
                            if (!empty($pluginInstantMessengerIntegrationWidgetSettings) && array_key_exists('displayInstantMessagingLinks', $pluginInstantMessengerIntegrationWidgetSettings)) {
                                $displayInstantMessagingLinks = $pluginInstantMessengerIntegrationWidgetSettings['displayInstantMessagingLinks'];
                                if (!empty($displayInstantMessagingLinks)) {
                                    /** @var Twig_Environment $twig */
                                    $twig = $this->container->get('twig');
                                    if ($twig !== null) {
                                        try {
                                            echo $twig->render('@InstantMessengerIntegration/legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig', ['displayInstantMessagingLinks' => $displayInstantMessagingLinks]);
                                        } catch (Twig_Error_Loader $e) {
                                            if ($this->devEnvironment) {
                                                echo '<br><div class="widget-options">Error on template load.</div>';
                                            } else {
                                                if ($logger!==null) {
                                                    $logger->error("Load error on template 'legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig'.", ['exception' => $e]);
                                                }
                                            }
                                        } catch (Twig_Error_Runtime $e) {
                                            if ($this->devEnvironment) {
                                                echo '<br><div class="widget-options">Error on run template.</div>';
                                            } else {
                                                if ($logger!==null) {
                                                    $logger->error("Runtime error on template 'legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig'.", ['exception' => $e]);
                                                }
                                            }
                                        } catch (Twig_Error_Syntax $e) {
                                            if ($this->devEnvironment) {
                                                echo '<br><div class="widget-options">Error on template syntax.</div>';
                                            } else {
                                                if ($logger!==null) {
                                                    $logger->error("Syntax error on template 'legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig'.", ['exception' => $e]);
                                                }
                                            }
                                        }
                                    }
                                    unset($twig);
                                }
                                unset($displayInstantMessagingLinks);
                            }
                            unset($pluginInstantMessengerIntegrationWidgetSettings);
                        }
                        unset($widgetContent);
                    }
                    unset($widgetType,
                        $widgetTitle);
                }
                unset($originalWidget,
                    $pageWidget);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getFormDesignSettingsAfterRenderFooterSpecificBlock method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getFormDesignSettingsAfterRenderHeaderSpecificBlock - Method that renders the specific plugins fieds in the widget configuration of header with phone.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getFormDesignSettingsAfterRenderHeaderSpecificBlock(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $originalWidget = $params['originalWidget'];
                $pageWidget = $params['pageWidget'];
                if (!empty($originalWidget) && !empty($pageWidget) && is_object($originalWidget) && method_exists($originalWidget, 'getType') && method_exists($originalWidget, 'getTitle')) {
                    $widgetType = $originalWidget->getType();
                    $widgetTitle = $originalWidget->getTitle();
                    $translator = $this->container->get('translator');
                    if (!empty($widgetType) && !empty($widgetTitle) && Widget::HEADER_TYPE === $widgetType && Widget::HEADER_WITH_CONTACT_PHONE === $widgetTitle) {
                        $instantMessengerTypes = array(
                            'whatsapp' => !empty($translator) ? ($translator->trans('WhatsApp')) : 'WhatsApp',
                            'telegram' => !empty($translator) ? ($translator->trans('Telegram')) : 'Telegram',
                            'messenger' => !empty($translator) ? ($translator->trans('Facebook Messenger')) : 'Facebook Messenger',
                        );
                        $displayInstantMessengerLinkAndPhone = 'phone';
                        $instantMessengerTypeToDisplay = 'whatsapp';
                        $widgetContent = $params['content'];
                        if (!empty($widgetContent) && is_array($widgetContent) && array_key_exists('pluginInstantMessengerIntegrationWidgetSettings', $widgetContent)) {
                            $pluginInstantMessengerIntegrationWidgetSettings = $widgetContent['pluginInstantMessengerIntegrationWidgetSettings'];
                            if (!empty($pluginInstantMessengerIntegrationWidgetSettings) && array_key_exists('displayInstantMessengerLinkAndPhone', $pluginInstantMessengerIntegrationWidgetSettings) && array_key_exists('instantMessengerTypeToDisplay', $pluginInstantMessengerIntegrationWidgetSettings)) {
                                $displayInstantMessengerLinkAndPhoneFromContent = $pluginInstantMessengerIntegrationWidgetSettings['displayInstantMessengerLinkAndPhone'];
                                $instantMessengerTypeToDisplayFromContent = $pluginInstantMessengerIntegrationWidgetSettings['instantMessengerTypeToDisplay'];
                                if (!empty($displayInstantMessengerLinkAndPhoneFromContent)) {
                                    $displayInstantMessengerLinkAndPhone = $displayInstantMessengerLinkAndPhoneFromContent;
                                }
                                if (!empty($instantMessengerTypeToDisplayFromContent) && array_key_exists($instantMessengerTypeToDisplayFromContent, $instantMessengerTypes)) {
                                    $instantMessengerTypeToDisplay = $instantMessengerTypeToDisplayFromContent;
                                }
                            }
                        }

                        /** @var Twig_Environment $twig */
                        $twig = $this->container->get('twig');
                        if ($twig !== null) {
                            try {
                                echo $twig->render('@InstantMessengerIntegration/legacy-sitemgr-form-design-settings-instant-messenger-header-options.html.twig', ['instantMessengerTypes' => $instantMessengerTypes, 'displayInstantMessengerLinkAndPhone' => $displayInstantMessengerLinkAndPhone, 'instantMessengerTypeToDisplay' => $instantMessengerTypeToDisplay]);
                            } catch (Twig_Error_Loader $e) {
                                if ($this->devEnvironment) {
                                    echo '<br><div class="widget-options">Error on template load.</div>';
                                } else {
                                    if ($logger!==null) {
                                        $logger->error("Load error on template 'legacy-sitemgr-form-design-settings-instant-messenger-header-options.html.twig'.", ['exception' => $e]);
                                    }
                                }
                            } catch (Twig_Error_Runtime $e) {
                                if ($this->devEnvironment) {
                                    echo '<br><div class="widget-options">Error on run template.</div>';
                                } else {
                                    if ($logger!==null) {
                                        $logger->error("Runtime error on template 'legacy-sitemgr-form-design-settings-instant-messenger-header-options.html.twig'.", ['exception' => $e]);
                                    }
                                }
                            } catch (Twig_Error_Syntax $e) {
                                if ($this->devEnvironment) {
                                    echo '<br><div class="widget-options">Error on template syntax.</div>';
                                } else {
                                    if ($logger!==null) {
                                        $logger->error("Syntax error on template 'legacy-sitemgr-form-design-settings-instant-messenger-header-options.html.twig'.", ['exception' => $e]);
                                    }
                                }
                            }
                        }
                        unset($twig);
                    }
                    unset($widgetTitle,
                        $widgetType);
                }
                unset($originalWidget,
                    $pageWidget);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getFormDesignSettingsAfterRenderHeaderSpecificBlock method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getEditContactFormModalAfterRenderGenericInputs - Method responsible to render the instant messenger plugin integration widget options in the ContactForm Widget.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getEditContactFormModalAfterRenderGenericInputs(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $widgetContent = $params['content'];
                if (!empty($widgetContent) && is_array($widgetContent) && array_key_exists('pluginInstantMessengerIntegrationWidgetSettings', $widgetContent)) {
                    $pluginInstantMessengerIntegrationWidgetSettings = $widgetContent['pluginInstantMessengerIntegrationWidgetSettings'];
                    if (!empty($pluginInstantMessengerIntegrationWidgetSettings) && array_key_exists('displayInstantMessagingLinks', $pluginInstantMessengerIntegrationWidgetSettings)) {
                        $displayInstantMessagingLinks = $pluginInstantMessengerIntegrationWidgetSettings['displayInstantMessagingLinks'];
                        if (!empty($displayInstantMessagingLinks)) {
                            /** @var Twig_Environment $twig */
                            $twig = $this->container->get('twig');
                            if ($twig !== null) {
                                try {
                                    echo $twig->render('@InstantMessengerIntegration/legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig', ['displayInstantMessagingLinks' => $displayInstantMessagingLinks]);
                                } catch (Twig_Error_Loader $e) {
                                    if ($this->devEnvironment) {
                                        echo '<br><div class="widget-options">Error on template load.</div>';
                                    } else {
                                        if ($logger!==null) {
                                            $logger->error("Load error on template 'legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig'.", ['exception' => $e]);
                                        }
                                    }
                                } catch (Twig_Error_Runtime $e) {
                                    if ($this->devEnvironment) {
                                        echo '<br><div class="widget-options">Error on run template.</div>';
                                    } else {
                                        if ($logger!==null) {
                                            $logger->error("Runtime error on template 'legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig'.", ['exception' => $e]);
                                        }
                                    }
                                } catch (Twig_Error_Syntax $e) {
                                    if ($this->devEnvironment) {
                                        echo '<br><div class="widget-options">Error on template syntax.</div>';
                                    } else {
                                        if ($logger!==null) {
                                            $logger->error("Syntax error on template 'legacy-sitemgr-form-design-settings-instant-messenger-footer-options.html.twig'.", ['exception' => $e]);
                                        }
                                    }
                                }
                            }
                            unset($twig);
                        }
                        unset($displayInstantMessagingLinks);
                    }
                    unset($pluginInstantMessengerIntegrationWidgetSettings);
                }
                unset($widgetContent);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getEditContactFormModalAfterRenderGenericInputs method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getWidgetServiceGetGenericLabelInputsAfterSetExceptionsKeys - Method tha add the plugin related form field in the exception key array to avoid the creation of the hidden fields in the widget configuration form.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getWidgetServiceGetGenericLabelInputsAfterSetExceptionsKeys(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $widgetContent = $params['content'];
                if (!empty($widgetContent) && is_array($widgetContent) && array_key_exists('pluginInstantMessengerIntegrationWidgetSettings', $widgetContent)) {
                    if (isset($params['exceptionsKeys']) && is_array($params['exceptionsKeys'])) {
                        $exceptionsKeysRef = &$params['exceptionsKeys'];
                        $exceptionsKeysRef[] = 'pluginInstantMessengerIntegrationWidgetSettings';
                    }
                }
                unset($widgetContent);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getWidgetServiceGetGenericLabelInputsAfterSetExceptionsKeys method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getWidgetActionAjaxAfterLoad - Method responsible to prepare the widget settings data to consider the plugin related information from pagewidget content, to be used in the form.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getWidgetActionAjaxAfterLoad(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['http_get_array']) && is_array($params['http_get_array']) && isset($params['returnArray'])) {
                    $httpGetArray = $params['http_get_array'];
                    $returnArrayRef = &$params['returnArray'];
                    if (empty($returnArrayRef)) {
                        $returnArrayRef = array();
                    }
                    if (array_key_exists('widgetId', $httpGetArray)) {
                        $widgetId = $httpGetArray['widgetId'];
                        if (!empty($widgetId)) {
                            $doctrine = $this->container->get('doctrine');
                            if (!empty($doctrine)) {
                                /** @var WidgetRepository $widgetRepository */
                                $widgetRepository = $doctrine->getRepository('WysiwygBundle:Widget');
                                if ($widgetRepository !== null) {
                                    /** @var Widget $widget */
                                    $widget = $widgetRepository->findOneBy([
                                        'id' => $widgetId,
                                    ]);
                                    if ($widget !== null) {
                                        $widgetType = $widget->getType();
                                        $widgetTitle = $widget->getTitle();
                                        if (!empty($widgetType) && !empty($widgetTitle)) {
                                            if (!empty($returnArrayRef) && is_array($returnArrayRef) && array_key_exists('content', $returnArrayRef)) {
                                                $encodedJsonWidgetContentString = $returnArrayRef['content'];
                                                if (!empty($encodedJsonWidgetContentString)) {
                                                    $decodedJsonWidgetContentString = json_decode($encodedJsonWidgetContentString, false);
                                                    if (!$this::foundJsonError($logger, $notLoggedCriticalException, 'Unexpected json error after decode json on getWidgetActionAjaxAfterLoad method of InstantMessengerIntegrationBundle.php')) {
                                                        $instantMessengerWidgetConfiguration = null;
                                                        switch ($widgetType) {
                                                            case Widget::HEADER_TYPE:
                                                                if (Widget::HEADER_WITH_CONTACT_PHONE === $widgetTitle) {
                                                                    $instantMessengerWidgetConfiguration = (object)[
                                                                        'displayInstantMessengerLinkAndPhone' => 'phone', //phone, both, instant_messenger
                                                                        'instantMessengerTypeToDisplay' => 'whatsapp', //whatsapp, telegram, messenger
                                                                    ];
                                                                    if (!empty($decodedJsonWidgetContentString)) {
                                                                        if (property_exists($decodedJsonWidgetContentString, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                                                                            if (property_exists($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessengerLinkAndPhone')) {
                                                                                if (in_array($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessengerLinkAndPhone, array('phone', 'both', 'instant_messenger'))) {
                                                                                    $instantMessengerWidgetConfiguration->displayInstantMessengerLinkAndPhone = $decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessengerLinkAndPhone;
                                                                                }
                                                                            }
                                                                            if (property_exists($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings, 'instantMessengerTypeToDisplay')) {
                                                                                if (in_array($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->instantMessengerTypeToDisplay, array('messenger', 'telegram', 'whatsapp'))) {
                                                                                    $instantMessengerWidgetConfiguration->instantMessengerTypeToDisplay = $decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->instantMessengerTypeToDisplay;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                break;
                                                            case Widget::FOOTER_TYPE:
                                                                if (in_array($widgetTitle, array(Widget::FOOTER, Widget::FOOTER_WITH_LOGO, Widget::FOOTER_WITH_SOCIAL_MEDIA, Widget::FOOTER_WITH_NEWSLETTER), true)) {
                                                                    $instantMessengerWidgetConfiguration = (object)[
                                                                        'displayInstantMessagingLinks' => 'off', //on, off
                                                                    ];
                                                                    if (!empty($decodedJsonWidgetContentString)) {
                                                                        if (property_exists($decodedJsonWidgetContentString, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                                                                            if (property_exists($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessagingLinks')) {
                                                                                if (in_array($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks, array('on', 'off'))) {
                                                                                    $instantMessengerWidgetConfiguration->displayInstantMessagingLinks = $decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                break;
                                                            case Widget::COMMON_TYPE:
                                                                if (Widget::CONTACT_FORM === $widgetTitle) {
                                                                    $instantMessengerWidgetConfiguration = (object)[
                                                                        'displayInstantMessagingLinks' => 'off', //on, off
                                                                    ];
                                                                    if (!empty($decodedJsonWidgetContentString)) {
                                                                        if (property_exists($decodedJsonWidgetContentString, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                                                                            if (property_exists($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessagingLinks')) {
                                                                                if (in_array($decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks, array('on', 'off'))) {
                                                                                    $instantMessengerWidgetConfiguration->displayInstantMessagingLinks = $decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                break;
                                                        }
                                                        if (!empty($instantMessengerWidgetConfiguration)) {
                                                            $decodedJsonWidgetContentString->pluginInstantMessengerIntegrationWidgetSettings = $instantMessengerWidgetConfiguration;
                                                            $encodedJsonWidgetContentStringAfterEnsureData = json_encode($decodedJsonWidgetContentString);
                                                            if (!$this::foundJsonError($logger, $notLoggedCriticalException, 'Unexpected json error after encode json on getWidgetActionAjaxAfterLoad method of InstantMessengerIntegrationBundle.php')) {
                                                                $returnArrayRef['content'] = $encodedJsonWidgetContentStringAfterEnsureData;
                                                            }
                                                        }
                                                    }
                                                    unset($decodedJsonWidgetContentString);
                                                }
                                                unset($encodedJsonWidgetContentString);
                                            }
                                        }
                                        unset($widgetType,
                                            $widgetTitle);
                                    }
                                    unset($widget);
                                }
                                unset($widgetRepository);
                            }
                            unset($doctrine);
                        }
                        unset($widgetId);
                    }
                    unset($httpGetArray);
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getWidgetActionAjaxAfterLoad method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getWidgetActionAjaxBeforeSave - Deal with the widget action before call the save function, ensure to extract the plugin related values, copy saveWidgetForAllPages value from contentArr post var and inject them in the post array itself.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getWidgetActionAjaxBeforeSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (isset($params['http_post_array']) && is_array($params['http_post_array'])) {
                    $httpPostArrayRef = &$params['http_post_array'];
                    if (array_key_exists('widgetId', $httpPostArrayRef)) {
                        $widgetId = $httpPostArrayRef['widgetId'];
                        if (!empty($widgetId)) {
                            $doctrine = $this->container->get('doctrine');
                            if (!empty($doctrine)) {
                                /** @var WidgetRepository $widgetRepository */
                                $widgetRepository = $doctrine->getRepository('WysiwygBundle:Widget');
                                if ($widgetRepository !== null) {
                                    /** @var Widget $widget */
                                    $widget = $widgetRepository->findOneBy([
                                        'id' => $widgetId,
                                    ]);
                                    if ($widget !== null) {
                                        $widgetType = $widget->getType();
                                        $widgetTitle = $widget->getTitle();
                                        if (!empty($widgetType) && !empty($widgetTitle)) {
                                            if (array_key_exists('contentArr', $httpPostArrayRef)) {
                                                $entryToIncludeInPost = null;
                                                $encodedJsonWidgetContentString = $httpPostArrayRef['contentArr'];
                                                if (!empty($encodedJsonWidgetContentString)) {
                                                    $decodedJsonWidgetContent = json_decode($encodedJsonWidgetContentString, false);
                                                    if (!$this::foundJsonError($logger, $notLoggedCriticalException, 'Unexpected json error on getWidgetActionAjaxBeforeSave method of InstantMessengerIntegrationBundle.php')) {
                                                        if (!empty($decodedJsonWidgetContent) && is_array($decodedJsonWidgetContent)) {
                                                            $itemsToRemove = [];
                                                            if (Widget::HEADER_TYPE === $widgetType && Widget::HEADER_WITH_CONTACT_PHONE === $widgetTitle) {
                                                                foreach ($decodedJsonWidgetContent as $key => $decodedJsonWidgetContentItem) {
                                                                    if (is_object($decodedJsonWidgetContentItem) && property_exists($decodedJsonWidgetContentItem, 'name') && property_exists($decodedJsonWidgetContentItem, 'value')) {
                                                                        if ('plugin[instant_messenger_integration][display_instant_messenger_link_and_phone]' === $decodedJsonWidgetContentItem->name) {
                                                                            $itemsToRemove[$key] = $decodedJsonWidgetContentItem;
                                                                            $entryToIncludeInPost = empty($entryToIncludeInPost) ? [] : $entryToIncludeInPost;
                                                                            $entryToIncludeInPost['instant_messenger_integration'] = empty($entryToIncludeInPost['instant_messenger_integration']) ? [] : $entryToIncludeInPost['instant_messenger_integration'];
                                                                            $entryToIncludeInPost['instant_messenger_integration']['display_instant_messenger_link_and_phone'] = $decodedJsonWidgetContentItem->value;
                                                                        } elseif ('plugin[instant_messenger_integration][instant_messenger_type_to_display]' === $decodedJsonWidgetContentItem->name) {
                                                                            $itemsToRemove[$key] = $decodedJsonWidgetContentItem;
                                                                            $entryToIncludeInPost = empty($entryToIncludeInPost) ? [] : $entryToIncludeInPost;
                                                                            $entryToIncludeInPost['instant_messenger_integration'] = empty($entryToIncludeInPost['instant_messenger_integration']) ? [] : $entryToIncludeInPost['instant_messenger_integration'];
                                                                            $entryToIncludeInPost['instant_messenger_integration']['instant_messenger_type_to_display'] = $decodedJsonWidgetContentItem->value;
                                                                        } elseif ('saveWidgetForAllPages' === $decodedJsonWidgetContentItem->name) {
                                                                            $httpPostArrayRef['saveWidgetForAllPages'] = $decodedJsonWidgetContentItem->value;
                                                                        }
                                                                    }
                                                                }
                                                                if (empty($entryToIncludeInPost['instant_messenger_integration'])) {
                                                                    $entryToIncludeInPost['instant_messenger_integration'] = [];
                                                                }
                                                                if (empty($entryToIncludeInPost['instant_messenger_integration']['display_instant_messenger_link_and_phone'])) {
                                                                    $entryToIncludeInPost['instant_messenger_integration']['display_instant_messenger_link_and_phone'] = 'phone';
                                                                }
                                                                if (empty($entryToIncludeInPost['instant_messenger_integration']['instant_messenger_type_to_display'])) {
                                                                    $entryToIncludeInPost['instant_messenger_integration']['instant_messenger_type_to_display'] = 'whatsapp';
                                                                }
                                                            } elseif ((Widget::COMMON_TYPE === $widgetType && Widget::CONTACT_FORM === $widgetTitle) || (Widget::FOOTER_TYPE === $widgetType && in_array($widgetTitle, array(Widget::FOOTER, Widget::FOOTER_WITH_LOGO, Widget::FOOTER_WITH_SOCIAL_MEDIA, Widget::FOOTER_WITH_NEWSLETTER), true))) {
                                                                foreach ($decodedJsonWidgetContent as $key => $decodedJsonWidgetContentItem) {
                                                                    if (is_object($decodedJsonWidgetContentItem) && property_exists($decodedJsonWidgetContentItem, 'name') && property_exists($decodedJsonWidgetContentItem, 'value')) {
                                                                        if ('plugin[instant_messenger_integration][display_instant_messaging_links]' === $decodedJsonWidgetContentItem->name) {
                                                                            $itemsToRemove[$key] = $decodedJsonWidgetContentItem;
                                                                            $entryToIncludeInPost = empty($entryToIncludeInPost) ? [] : $entryToIncludeInPost;
                                                                            $entryToIncludeInPost['instant_messenger_integration'] = empty($entryToIncludeInPost['instant_messenger_integration']) ? [] : $entryToIncludeInPost['instant_messenger_integration'];
                                                                            $entryToIncludeInPost['instant_messenger_integration']['display_instant_messaging_links'] = $decodedJsonWidgetContentItem->value;
                                                                        } elseif ('saveWidgetForAllPages' === $decodedJsonWidgetContentItem->name) {
                                                                            $httpPostArrayRef['saveWidgetForAllPages'] = $decodedJsonWidgetContentItem->value;
                                                                        }
                                                                    }
                                                                }
                                                                if (empty($entryToIncludeInPost['instant_messenger_integration'])) {
                                                                    $entryToIncludeInPost['instant_messenger_integration'] = [];
                                                                }
                                                                if (empty($entryToIncludeInPost['instant_messenger_integration']['display_instant_messaging_links'])) {
                                                                    $entryToIncludeInPost['instant_messenger_integration']['display_instant_messaging_links'] = 'off';
                                                                }
                                                            }
                                                            if (!empty($itemsToRemove)) {
                                                                $decodedJsonWidgetContent = array_diff_key($decodedJsonWidgetContent, $itemsToRemove);
                                                                $encodedJsonWidgetContentStringAfterEnsureData = json_encode($decodedJsonWidgetContent);
                                                                if (!$this::foundJsonError($logger, $notLoggedCriticalException, 'Unexpected json error on getWidgetActionAjaxBeforeSave method of InstantMessengerIntegrationBundle.php', $encodedJsonWidgetContentStringAfterEnsureData)) {
                                                                    $httpPostArrayRef['contentArr'] = $encodedJsonWidgetContentStringAfterEnsureData;
                                                                }
                                                                unset($encodedJsonWidgetContentStringAfterEnsureData);
                                                            }
                                                            unset($itemsToRemove);
                                                        }
                                                    }
                                                    unset($decodedJsonWidgetContent);
                                                }
                                                if (!empty($entryToIncludeInPost)) {
                                                    $httpPostArrayRef['plugin'] = $entryToIncludeInPost;
                                                }
                                                unset($encodedJsonWidgetContentString);
                                            }
                                        }
                                        unset($widgetType,
                                            $widgetTitle);
                                    }
                                    unset($widget);
                                }
                                unset($widgetRepository);
                            }
                            unset($doctrine);
                        }
                        unset($widgetId);
                    }
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getWidgetActionAjaxBeforeSave method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getWidgetActionAjaxAfterSave - Deal with the widget action after call the save function, deal with the plugin related post variables to save the value on the PageWidget content.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getWidgetActionAjaxAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $httpPostArray = $params['http_post_array'];
                if (!empty($httpPostArray) && is_array($httpPostArray) && array_key_exists('plugin', $httpPostArray)) {
                    $pluginPostArray = $httpPostArray['plugin'];
                    $saveWidgetForAllPages = false;
                    if (array_key_exists('saveWidgetForAllPages', $httpPostArray)) {
                        $saveWidgetForAllPages = !empty($httpPostArray['saveWidgetForAllPages']) && '1' === $httpPostArray['saveWidgetForAllPages'];
                    }
                    if (!empty($pluginPostArray) && is_array($pluginPostArray) && array_key_exists('instant_messenger_integration', $pluginPostArray)) {
                        $instantMessengerIntegrationPluginPostArray = $pluginPostArray['instant_messenger_integration'];
                        if (!empty($instantMessengerIntegrationPluginPostArray) && is_array($instantMessengerIntegrationPluginPostArray)) {
                            $returnArray = $params['return'];
                            if (!empty($returnArray) && is_array($returnArray)) {
                                $pageWidgetId = $returnArray['newWidgetId'];
                                if (!empty($pageWidgetId)) {
                                    $doctrine = $this->container->get('doctrine');
                                    if (!empty($doctrine)) {
                                        /** @var EntityManager $em */
                                        $em = $doctrine->getManager();
                                        if ($em !== null) {
                                            /** @var PageWidgetRepository $pageWidgetRepository */
                                            $pageWidgetRepository = $doctrine->getRepository('WysiwygBundle:PageWidget');
                                            $widgetRepository = $doctrine->getRepository('WysiwygBundle:Widget');
                                            if ($pageWidgetRepository !== null && !empty($widgetRepository)) {
                                                /** @var PageWidget $pageWidget */
                                                $pageWidget = $pageWidgetRepository->findOneBy([
                                                    'id' => $pageWidgetId,
                                                ]);
                                                if ($pageWidget !== null) {
                                                    $widgetId = $pageWidget->getWidgetId();
                                                    $encodedPageWidgetContent = $pageWidget->getContent();
                                                    if (!empty($widgetId)) {
                                                        /** @var Widget */
                                                        $widget = $widgetRepository->findOneBy([
                                                            'id' => $widgetId,
                                                        ]);
                                                        if (!empty($widget)) {
                                                            $widgetType = $widget->getType();
                                                            $widgetTitle = $widget->getTitle();
                                                            if (!empty($widgetType) && !empty($widgetTitle)) {
                                                                $decodedPageWidgetContent = (object)[];
                                                                $contentWithError = false;
                                                                if (!empty($encodedPageWidgetContent)) {
                                                                    $decodedPageWidgetContent = json_decode($encodedPageWidgetContent, false);
                                                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                                                        $e = new Exception(json_last_error_msg(), json_last_error());
                                                                        if ($logger!==null) {
                                                                            $logger->critical('Unexpected json error on getWidgetActionAjaxAfterSave method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                                                                        } else {
                                                                            $notLoggedCriticalException = $e;
                                                                        }
                                                                        $contentWithError = true;
                                                                    }
                                                                }
                                                                if (!$contentWithError && is_object($decodedPageWidgetContent)) {
                                                                    $instantMessengerWidgetConfiguration = null;
                                                                    if (Widget::HEADER_TYPE === $widgetType && Widget::HEADER_WITH_CONTACT_PHONE === $widgetTitle) {
                                                                        if (array_key_exists('display_instant_messenger_link_and_phone', $instantMessengerIntegrationPluginPostArray) && array_key_exists('instant_messenger_type_to_display', $instantMessengerIntegrationPluginPostArray)) {
                                                                            $displayInstantMessengerLinkAndPhoneValueFromForm = $instantMessengerIntegrationPluginPostArray['display_instant_messenger_link_and_phone'];
                                                                            $instantMessengerTypeToDisplayValueFromForm = $instantMessengerIntegrationPluginPostArray['instant_messenger_type_to_display'];
                                                                            $instantMessengerWidgetConfiguration = (object)[
                                                                                'displayInstantMessengerLinkAndPhone' => 'phone', //phone, both, instant_messenger
                                                                                'instantMessengerTypeToDisplay' => 'whatsapp', //whatsapp, telegram, messenger
                                                                            ];
                                                                            if (!empty($displayInstantMessengerLinkAndPhoneValueFromForm) && in_array($displayInstantMessengerLinkAndPhoneValueFromForm, array('phone', 'both', 'instant_messenger'))) {
                                                                                $instantMessengerWidgetConfiguration->displayInstantMessengerLinkAndPhone = $displayInstantMessengerLinkAndPhoneValueFromForm;
                                                                            }
                                                                            unset($displayInstantMessengerLinkAndPhoneValueFromForm);
                                                                            if (!empty($instantMessengerTypeToDisplayValueFromForm) && in_array($instantMessengerTypeToDisplayValueFromForm, array('messenger', 'telegram', 'whatsapp'))) {
                                                                                $instantMessengerWidgetConfiguration->instantMessengerTypeToDisplay = $instantMessengerTypeToDisplayValueFromForm;
                                                                            }
                                                                            unset($instantMessengerTypeToDisplayValueFromForm);
                                                                        }
                                                                    } elseif ((Widget::COMMON_TYPE === $widgetType && Widget::CONTACT_FORM === $widgetTitle) || (Widget::FOOTER_TYPE === $widgetType && in_array($widgetTitle, array(Widget::FOOTER, Widget::FOOTER_WITH_LOGO, Widget::FOOTER_WITH_SOCIAL_MEDIA, Widget::FOOTER_WITH_NEWSLETTER), true))) {
                                                                        if (array_key_exists('display_instant_messaging_links', $instantMessengerIntegrationPluginPostArray)) {
                                                                            $displayInstantMessagingLinksValueFromForm = $instantMessengerIntegrationPluginPostArray['display_instant_messaging_links'];
                                                                            $instantMessengerWidgetConfiguration = (object)[
                                                                                'displayInstantMessagingLinks' => 'off',
                                                                            ];
                                                                            if (!empty($displayInstantMessagingLinksValueFromForm) && in_array($displayInstantMessagingLinksValueFromForm, array('on', 'off'))) {
                                                                                $instantMessengerWidgetConfiguration->displayInstantMessagingLinks = $displayInstantMessagingLinksValueFromForm;
                                                                            }
                                                                            unset($displayInstantMessagingLinksValueFromForm);
                                                                        }
                                                                    }
                                                                    if (!empty($instantMessengerWidgetConfiguration)) {
                                                                        $decodedPageWidgetContent->pluginInstantMessengerIntegrationWidgetSettings = $instantMessengerWidgetConfiguration;
                                                                        $encodedPageWidgetContentStringAfterUpdateData = json_encode($decodedPageWidgetContent);
                                                                        if (!$this::foundJsonError($logger, $notLoggedCriticalException, 'Unexpected json error on getWidgetActionAjaxAfterSave method of InstantMessengerIntegrationBundle.php')) {
                                                                            $pageWidget->setContent($encodedPageWidgetContentStringAfterUpdateData);
                                                                            $em->persist($pageWidget);
                                                                            $em->flush();
                                                                            if ($saveWidgetForAllPages) {
                                                                                $themeService = $this->container->get('theme.service');
                                                                                if (!empty($themeService)) {
                                                                                    $selectedTheme = $themeService->getSelectedTheme();
                                                                                    if (!empty($selectedTheme)) {
                                                                                        $themeId = $selectedTheme->getId();
                                                                                        if (!empty($themeId)) {
                                                                                            $pageWidgetRepository->updateWidgetContentForAllPages($widgetId, $themeId, $encodedPageWidgetContentStringAfterUpdateData);
                                                                                        }
                                                                                        unset($themeId);
                                                                                    }
                                                                                    unset($selectedTheme);
                                                                                }
                                                                                unset($themeService);
                                                                            }
                                                                            $em->flush();
                                                                        }
                                                                        unset($encodedPageWidgetContentStringAfterUpdateData);
                                                                    }
                                                                    unset($instantMessengerWidgetConfiguration);
                                                                }
                                                                unset($contentWithError,
                                                                    $decodedPageWidgetContent);
                                                            }
                                                            unset($widgetType,
                                                                $widgetTitle);
                                                        }
                                                        unset($widget);
                                                    }
                                                    unset($widgetId);
                                                }
                                                unset($pageWidget);
                                            }
                                            unset($pageWidgetRepository);
                                        }
                                        unset($em);
                                    }
                                    unset($doctrine);
                                }
                                unset($pageWidgetId);
                            }
                            unset($returnArray);
                        }
                        unset($instantMessengerIntegrationPluginPostArray);
                    }
                    unset($pluginPostArray);
                }
                unset($httpPostArray);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getWidgetActionAjaxAfterSave method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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

    const HEADER_TYPE_3_NAVBAR_TYPE_OUTSIDE = 'outside';
    const HEADER_TYPE_3_NAVBAR_TYPE_MOBILE = 'mobile';

    /**
     * getHeaderType3NavbarOverwritePhone - Method that will echo the block that overwrites the default phone block from the Header with Phone Widget on Mobile(Responsive) or Web view, considering the plugin settings and data.
     *
     * @param string $type
     * @param null $params
     * @throws Exception
     */
    public function getHeaderType3NavbarOverwritePhone($type, &$params = null): void
    {
        if ($type === $this::HEADER_TYPE_3_NAVBAR_TYPE_MOBILE || $type === $this::HEADER_TYPE_3_NAVBAR_TYPE_OUTSIDE) {
            if (!empty($params) && !empty($this->container)) {
                /** @var Logger $logger */
                $logger = $this->container->get('logger');
                $notLoggedCriticalException = null;
                try {
                    $phone = $params['phone'];
                    $content = $params['content'];
                    if (!empty($content) && is_object($content) && property_exists($content, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                        $pluginInstantMessengerIntegrationWidgetSettings = $content->pluginInstantMessengerIntegrationWidgetSettings;
                        if (!empty($pluginInstantMessengerIntegrationWidgetSettings) &&
                            is_object($pluginInstantMessengerIntegrationWidgetSettings) &&
                            property_exists($pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessengerLinkAndPhone') &&
                            property_exists($pluginInstantMessengerIntegrationWidgetSettings, 'instantMessengerTypeToDisplay')) {
                            $displayInstantMessengerLinkAndPhone = $pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessengerLinkAndPhone;
                            $instantMessengerTypeToDisplay = $pluginInstantMessengerIntegrationWidgetSettings->instantMessengerTypeToDisplay;
                            if (!empty($displayInstantMessengerLinkAndPhone) && !empty($instantMessengerTypeToDisplay)) {
                                $settings = $this->container->get('settings');
                                /** @var Twig_Environment $twig */
                                $twig = $this->container->get('twig');
                                /** @var InstantMessengerService $instantMessengerService */
                                $instantMessengerService = $this->container->get('instantmessenger.service');
                                if (!empty($settings) && $twig !== null && $instantMessengerService !== null) {
                                    $lang = 'en';
                                    /**
                                     * @var LanguageHandler $languageHandler
                                     */
                                    $languageHandler = $this->container->get('languagehandler');
                                    if ($languageHandler !== null) {
                                        $multiDomainInfo = $this->container->get('multi_domain.information');
                                        if (!empty($multiDomainInfo)) {
                                            $domainLocale = $multiDomainInfo->getLocale();
                                            $lang = $languageHandler->getISOLang($domainLocale);
                                            unset($domainLocale);
                                        }
                                        unset($multiDomainInfo);
                                    }
                                    unset($languageHandler);
                                    $instantMessengerService->setLang($lang);
                                    $instantMessengerData = null;
                                    if (in_array($displayInstantMessengerLinkAndPhone, array('both', 'instant_messenger'))) {
                                        $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                        $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                                        if (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                                            try {
                                                $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                                                if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                                    $instantMessengerData = new InstantMessengerLinkButtonData();
                                                    $instantMessengerData->type = $instantMessengerTypeToDisplay;
                                                    $instantMessengerService->extractInstantMessengerLinkButtonDataFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerData);
                                                }
                                            } catch (InstantMessengerServiceException $e) {
                                                if ($logger!==null) {
                                                    $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                                } else {
                                                    $notLoggedCriticalException = $e->getPrevious();
                                                }
                                            }
                                        }
                                        unset($encodedJsonInstantMessengerSiteManagerData);
                                    }

                                    try {
                                        echo $twig->render('@InstantMessengerIntegration/header-type3-' . $type . '-overwrite-phone.html.twig', ['phone' => $phone, 'instantMessengerData' => $instantMessengerData, 'displayInstantMessengerLinkAndPhone' => $displayInstantMessengerLinkAndPhone]);
                                    } catch (Twig_Error_Loader $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template load.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Load error on template 'header-type3-'.$type.'-overwrite-phone.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Runtime $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on run template.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Runtime error on template 'header-type3-'.$type.'-overwrite-phone.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Syntax $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template syntax.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Syntax error on template 'header-type3-'.$type.'-overwrite-phone.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    }
                                }
                                unset($settings,
                                    $twig);
                            }
                            unset($instantMessengerTypeToDisplay,
                                $displayInstantMessengerLinkAndPhone);
                        }
                        unset($pluginInstantMessengerIntegrationWidgetSettings);
                    }
                    unset($content,
                        $phone);
                } catch (Exception $e) {
                    if ($logger!==null) {
                        $logger->critical('Unexpected error on getHeaderType3NavbarOverwritePhone method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
    }

    /**
     * getContactFormWillRenderContactUsInfo - Method that will determine if the block that renders the instant messaging data when the integration has been setup for the contact form widget, after the contact phone.
     *
     * @param null $params
     * @throws Exception
     */
    public function getContactFormWillRenderContactUsInfo(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $content = $params['content'];
                if (!empty($content) && is_object($content) && property_exists($content, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                    $pluginInstantMessengerIntegrationWidgetSettings = $content->pluginInstantMessengerIntegrationWidgetSettings;
                    if (!empty($pluginInstantMessengerIntegrationWidgetSettings) &&
                        is_object($pluginInstantMessengerIntegrationWidgetSettings) &&
                        property_exists($pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessagingLinks')) {
                        $displayInstantMessagingLinks = $pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks;
                        if (!empty($displayInstantMessagingLinks)) {
                            $settings = $this->container->get('settings');
                            /** @var Twig_Environment $twig */
                            $twig = $this->container->get('twig');
                            /** @var InstantMessengerService $instantMessengerService */
                            $instantMessengerService = $this->container->get('instantmessenger.service');

                            if (!empty($settings) && $twig !== null && $instantMessengerService !== null) {
                                $lang = 'en';
                                /**
                                 * @var LanguageHandler $languageHandler
                                 */
                                $languageHandler = $this->container->get('languagehandler');
                                if ($languageHandler !== null) {
                                    $multiDomainInfo = $this->container->get('multi_domain.information');
                                    if (!empty($multiDomainInfo)) {
                                        $domainLocale = $multiDomainInfo->getLocale();
                                        $lang = $languageHandler->getISOLang($domainLocale);
                                        unset($domainLocale);
                                    }
                                    unset($multiDomainInfo);
                                }
                                unset($languageHandler);
                                $instantMessengerService->setLang($lang);
                                $instantMessengerLinkButtonDataArray = null;
                                if ('on' === $displayInstantMessagingLinks) {
                                    $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                                    if (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                                        $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                        try {
                                            $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                                        } catch (InstantMessengerServiceException $e) {
                                            if ($logger!==null) {
                                                $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                            } else {
                                                $notLoggedCriticalException = $e->getPrevious();
                                            }
                                        }
                                        if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                            $instantMessengerLinkButtonDataArray = new InstantMessengerLinkButtonDataArray();
                                            $instantMessengerService->extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerLinkButtonDataArray);
                                        }
                                        unset($instantMessengerDataArray);
                                    }
                                    unset($encodedJsonInstantMessengerSiteManagerData);
                                }
                                $params['_return'] = ($instantMessengerLinkButtonDataArray !== null && $instantMessengerLinkButtonDataArray->count() > 0);
                            }
                            unset($settings,
                                $twig);
                        }
                        unset($instantMessengerTypeToDisplay,
                            $displayInstantMessengerLinkAndPhone);
                    }
                    unset($pluginInstantMessengerIntegrationWidgetSettings);
                }
                unset($content);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getContactFormWillRenderContactUsInfo method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getContactFormAfterPhoneRendering - Method that will echo the block that renders the instant messaging data when the integration has been setup for the contact form widget, after the contact phone.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getContactFormAfterPhoneRendering(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $content = $params['content'];
                if (!empty($content) && is_object($content) && property_exists($content, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                    $pluginInstantMessengerIntegrationWidgetSettings = $content->pluginInstantMessengerIntegrationWidgetSettings;
                    if (!empty($pluginInstantMessengerIntegrationWidgetSettings) &&
                        is_object($pluginInstantMessengerIntegrationWidgetSettings) &&
                        property_exists($pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessagingLinks')) {
                        $displayInstantMessagingLinks = $pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks;
                        if (!empty($displayInstantMessagingLinks)) {
                            $settings = $this->container->get('settings');
                            /** @var Twig_Environment $twig */
                            $twig = $this->container->get('twig');
                            /** @var InstantMessengerService $instantMessengerService */
                            $instantMessengerService = $this->container->get('instantmessenger.service');

                            if (!empty($settings) && $twig !== null && $instantMessengerService !== null) {
                                $lang = 'en';
                                /**
                                 * @var LanguageHandler $languageHandler
                                 */
                                $languageHandler = $this->container->get('languagehandler');
                                if ($languageHandler !== null) {
                                    $multiDomainInfo = $this->container->get('multi_domain.information');
                                    if (!empty($multiDomainInfo)) {
                                        $domainLocale = $multiDomainInfo->getLocale();
                                        $lang = $languageHandler->getISOLang($domainLocale);
                                        unset($domainLocale);
                                    }
                                    unset($multiDomainInfo);
                                }
                                unset($languageHandler);
                                $instantMessengerService->setLang($lang);
                                $instantMessengerLinkButtonDataArray = null;
                                if ('on' === $displayInstantMessagingLinks) {
                                    $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                                    if (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                                        $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                        try {
                                            $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                                        } catch (InstantMessengerServiceException $e) {
                                            if ($logger!==null) {
                                                $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                            } else {
                                                $notLoggedCriticalException = $e->getPrevious();
                                            }
                                        }
                                        if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                            $instantMessengerLinkButtonDataArray = new InstantMessengerLinkButtonDataArray();
                                            $instantMessengerService->extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerLinkButtonDataArray);
                                        }
                                        unset($instantMessengerDataArray);
                                    }
                                    unset($encodedJsonInstantMessengerSiteManagerData);
                                }
                                if ($instantMessengerLinkButtonDataArray !== null && $instantMessengerLinkButtonDataArray->count() > 0) {
                                    try {
                                        echo $twig->render('@InstantMessengerIntegration/contact-form-instant-messenger-integration-links.html.twig', ['instantMessengerDataArray' => $instantMessengerLinkButtonDataArray, 'displayInstantMessagingLinks' => $displayInstantMessagingLinks]);
                                    } catch (Twig_Error_Loader $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template load.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Load error on template 'contact-form-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Runtime $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on run template.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Runtime error on template 'contact-form-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Syntax $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template syntax.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Syntax error on template 'contact-form-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    }
                                }
                            }
                            unset($settings,
                                $twig);
                        }
                        unset($instantMessengerTypeToDisplay,
                            $displayInstantMessengerLinkAndPhone);
                    }
                    unset($pluginInstantMessengerIntegrationWidgetSettings);
                }
                unset($content);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getContactFormAfterPhoneRendering method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getViewsBlocksContactUsWillRender - Method that will indicate if the plugin demands that contactus footer block need to be rendered when the integration has been setup for the footer widgets: Footer, Footer with Logo and Footer with Newsletter.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getViewsBlocksContactUsWillRender(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $willRender = false;
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $content = $params['content'];
                if (!empty($content) && is_object($content) && property_exists($content, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                    $pluginInstantMessengerIntegrationWidgetSettings = $content->pluginInstantMessengerIntegrationWidgetSettings;
                    if (!empty($pluginInstantMessengerIntegrationWidgetSettings) &&
                        is_object($pluginInstantMessengerIntegrationWidgetSettings) &&
                        property_exists($pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessagingLinks')) {
                        $displayInstantMessagingLinks = $pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks;
                        if (!empty($displayInstantMessagingLinks)) {
                            $settings = $this->container->get('settings');
                            /** @var InstantMessengerService $instantMessengerService */
                            $instantMessengerService = $this->container->get('instantmessenger.service');
                            if (!empty($settings) && $instantMessengerService !== null) {
                                $lang = 'en';
                                /**
                                 * @var LanguageHandler $languageHandler
                                 */
                                $languageHandler = $this->container->get('languagehandler');
                                if ($languageHandler !== null) {
                                    $multiDomainInfo = $this->container->get('multi_domain.information');
                                    if (!empty($multiDomainInfo)) {
                                        $domainLocale = $multiDomainInfo->getLocale();
                                        $lang = $languageHandler->getISOLang($domainLocale);
                                        unset($domainLocale);
                                    }
                                    unset($multiDomainInfo);
                                }
                                unset($languageHandler);
                                $instantMessengerService->setLang($lang);
                                $instantMessengerLinkButtonDataArray = null;
                                if ($displayInstantMessagingLinks === 'on') {
                                    $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                                    if (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                                        $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                        try {
                                            $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                                        } catch (InstantMessengerServiceException $e) {
                                            if ($logger!==null) {
                                                $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                            } else {
                                                $notLoggedCriticalException = $e->getPrevious();
                                            }
                                        }
                                        if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                            $instantMessengerLinkButtonDataArray = new InstantMessengerLinkButtonDataArray();
                                            $instantMessengerService->extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerLinkButtonDataArray);
                                        }
                                        unset($instantMessengerDataArray);
                                    }
                                    unset($encodedJsonInstantMessengerSiteManagerData);
                                }
                                if ($instantMessengerLinkButtonDataArray !== null && $instantMessengerLinkButtonDataArray->count() > 0) {
                                    $willRender = true;
                                }
                            }
                            unset($settings, $instantMessengerService, $twig);
                        }
                        unset($instantMessengerTypeToDisplay,
                            $displayInstantMessengerLinkAndPhone);
                    }
                    unset($pluginInstantMessengerIntegrationWidgetSettings);
                }
                unset($content);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getContactUsAfterPhoneRendering method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
            $params['_return'] = $willRender;
        }
    }

    /**
     * getContactUsAfterPhoneRendering - Method that will echo the block that renders the instant messaging data when the integration has been setup for the footer widgets: Footer, Footer with Logo and Footer with Newsletter, after the contact phone.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getContactUsAfterPhoneRendering(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $content = $params['content'];
                if (!empty($content) && is_object($content) && property_exists($content, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                    $pluginInstantMessengerIntegrationWidgetSettings = $content->pluginInstantMessengerIntegrationWidgetSettings;
                    if (!empty($pluginInstantMessengerIntegrationWidgetSettings) &&
                        is_object($pluginInstantMessengerIntegrationWidgetSettings) &&
                        property_exists($pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessagingLinks')) {
                        $displayInstantMessagingLinks = $pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks;
                        if (!empty($displayInstantMessagingLinks)) {
                            $settings = $this->container->get('settings');
                            /** @var Twig_Environment $twig */
                            $twig = $this->container->get('twig');
                            /** @var InstantMessengerService $instantMessengerService */
                            $instantMessengerService = $this->container->get('instantmessenger.service');
                            if (!empty($settings) && $twig !== null && $instantMessengerService !== null) {
                                $lang = 'en';
                                /**
                                 * @var LanguageHandler $languageHandler
                                 */
                                $languageHandler = $this->container->get('languagehandler');
                                if ($languageHandler !== null) {
                                    $multiDomainInfo = $this->container->get('multi_domain.information');
                                    if (!empty($multiDomainInfo)) {
                                        $domainLocale = $multiDomainInfo->getLocale();
                                        $lang = $languageHandler->getISOLang($domainLocale);
                                        unset($domainLocale);
                                    }
                                    unset($multiDomainInfo);
                                }
                                unset($languageHandler);
                                $instantMessengerService->setLang($lang);
                                $instantMessengerLinkButtonDataArray = null;
                                if ('on' === $displayInstantMessagingLinks) {
                                    $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                                    if (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                                        $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                        try {
                                            $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                                        } catch (InstantMessengerServiceException $e) {
                                            if ($logger!==null) {
                                                $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                            } else {
                                                $notLoggedCriticalException = $e->getPrevious();
                                            }
                                        }
                                        if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                            $instantMessengerLinkButtonDataArray = new InstantMessengerLinkButtonDataArray();
                                            $instantMessengerService->extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerLinkButtonDataArray);
                                        }
                                        unset($instantMessengerDataArray);
                                    }
                                    unset($encodedJsonInstantMessengerSiteManagerData);
                                }
                                if ($instantMessengerLinkButtonDataArray !== null && $instantMessengerLinkButtonDataArray->count() > 0) {
                                    try {
                                        echo $twig->render('@InstantMessengerIntegration/contactus-instant-messenger-integration-links.html.twig', ['instantMessengerDataArray' => $instantMessengerLinkButtonDataArray, 'displayInstantMessagingLinks' => $displayInstantMessagingLinks]);
                                    } catch (Twig_Error_Loader $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template load.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Load error on template 'contactus-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Runtime $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on run template.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Runtime error on template 'contactus-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Syntax $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template syntax.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Syntax error on template 'contactus-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    }
                                }
                            }
                            unset($settings, $instantMessengerService, $twig);
                        }
                        unset($instantMessengerTypeToDisplay,
                            $displayInstantMessengerLinkAndPhone);
                    }
                    unset($pluginInstantMessengerIntegrationWidgetSettings);
                }
                unset($content);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getContactUsAfterPhoneRendering method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getFooterType3FooterContactAfterPhoneRendering - Method that will echo the block that renders the instant messaging data when the integration has been setup for the footer widget Footer with Social Media, after the contact phone.
     *
     * @param null $params
     *
     * @throws Exception
     */
    public function getFooterType3FooterContactAfterPhoneRendering(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $content = $params['content'];
                if (!empty($content) && is_object($content) && property_exists($content, 'pluginInstantMessengerIntegrationWidgetSettings')) {
                    $pluginInstantMessengerIntegrationWidgetSettings = $content->pluginInstantMessengerIntegrationWidgetSettings;
                    if (!empty($pluginInstantMessengerIntegrationWidgetSettings) &&
                        is_object($pluginInstantMessengerIntegrationWidgetSettings) &&
                        property_exists($pluginInstantMessengerIntegrationWidgetSettings, 'displayInstantMessagingLinks')) {
                        $displayInstantMessagingLinks = $pluginInstantMessengerIntegrationWidgetSettings->displayInstantMessagingLinks;
                        if (!empty($displayInstantMessagingLinks)) {
                            $settings = $this->container->get('settings');
                            /** @var Twig_Environment $twig */
                            $twig = $this->container->get('twig');
                            /** @var InstantMessengerService $instantMessengerService */
                            $instantMessengerService = $this->container->get('instantmessenger.service');
                            if (!empty($settings) && $twig !== null && $instantMessengerService !== null) {
                                $lang = 'en';
                                /**
                                 * @var LanguageHandler $languageHandler
                                 */
                                $languageHandler = $this->container->get('languagehandler');
                                if ($languageHandler !== null) {
                                    $multiDomainInfo = $this->container->get('multi_domain.information');
                                    if (!empty($multiDomainInfo)) {
                                        $domainLocale = $multiDomainInfo->getLocale();
                                        $lang = $languageHandler->getISOLang($domainLocale);
                                        unset($domainLocale);
                                    }
                                    unset($multiDomainInfo);
                                }
                                unset($languageHandler);
                                $instantMessengerService->setLang($lang);
                                $instantMessengerLinkButtonDataArray = null;
                                if ('on' === $displayInstantMessagingLinks) {
                                    $encodedJsonInstantMessengerSiteManagerData = $settings->getDomainSetting('plugin_instant_messenger_integration_site_manager_data');
                                    if (!empty($encodedJsonInstantMessengerSiteManagerData)) {
                                        $instantMessengerDataArray = new InstantMessengerDataArray(array(new FacebookMessengerData(), new TelegramData(), new WhatsappData()));
                                        try {
                                            $instantMessengerService->extractImDataFromEncodedJson($encodedJsonInstantMessengerSiteManagerData, $instantMessengerDataArray);
                                        } catch (InstantMessengerServiceException $e) {
                                            if ($logger!==null) {
                                                $logger->critical($e->getMessage(), ['exception' => $e->getPrevious()]);
                                            } else {
                                                $notLoggedCriticalException = $e->getPrevious();
                                            }
                                        }
                                        if (!$instantMessengerService->isImDataEmpty($instantMessengerDataArray)) {
                                            $instantMessengerLinkButtonDataArray = new InstantMessengerLinkButtonDataArray();
                                            $instantMessengerService->extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray($instantMessengerDataArray, $instantMessengerLinkButtonDataArray);
                                        }
                                        unset($instantMessengerDataArray);
                                    }
                                    unset($encodedJsonInstantMessengerSiteManagerData);
                                }
                                if ($instantMessengerLinkButtonDataArray !== null && $instantMessengerLinkButtonDataArray->count() > 0) {
                                    try {
                                        echo $twig->render('@InstantMessengerIntegration/footer-type3-instant-messenger-integration-links.html.twig', ['instantMessengerDataArray' => $instantMessengerLinkButtonDataArray, 'displayInstantMessagingLinks' => $displayInstantMessagingLinks]);
                                    } catch (Twig_Error_Loader $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template load.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Load error on template 'footer-type3-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Runtime $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on run template.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Runtime error on template 'footer-type3-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    } catch (Twig_Error_Syntax $e) {
                                        if ($this->devEnvironment) {
                                            echo '<div>Error on template syntax.</div>';
                                        } else {
                                            if ($logger!==null) {
                                                $logger->error("Syntax error on template 'footer-type3-instant-messenger-integration-links.html.twig'.", ['exception' => $e]);
                                            }
                                        }
                                    }
                                }
                            }
                            unset($settings, $instantMessengerService, $twig);
                        }
                        unset($instantMessengerTypeToDisplay,
                            $displayInstantMessengerLinkAndPhone);
                    }
                    unset($pluginInstantMessengerIntegrationWidgetSettings);
                }
                unset($content);
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getFooterType3FooterContactAfterPhoneRendering method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getLegacySitemgrContentListingBeforeModalIncludes - Method that allows include a new modal in listing form on sitemgr area
     *
     * @param null $params
     * @throws Exception
     */
    public function getLegacySitemgrContentListingBeforeModalIncludes(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                /** @var Twig_Environment $twig */
                $twig = $this->container->get('twig');
                if ($twig !== null) {
                    try {
                        echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig', []);
                    } catch (Twig_Error_Loader $e) {
                        if ($this->devEnvironment) {
                            echo '<div>Error on template load.</div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Load error on template 'legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Runtime $e) {
                        if ($this->devEnvironment) {
                            echo '<div>Error on run template.</div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Runtime error on template 'legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Syntax $e) {
                        if ($this->devEnvironment) {
                            echo '<div>Error on template syntax.</div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Syntax error on template 'legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig'.", ['exception' => $e]);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getLegacySitemgrContentListingBeforeModalIncludes method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getLegacySponsorsListingBeforeModalIncludes - Method that allows include a new modal in social media configuration page on sponsors area
     *
     * @param null $params
     * @throws Exception
     */
    public function getLegacySponsorsListingBeforeModalIncludes(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                /** @var Twig_Environment $twig */
                $twig = $this->container->get('twig');
                if ($twig !== null) {
                    try {
                        echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig', []);
                    } catch (Twig_Error_Loader $e) {
                        if ($this->devEnvironment) {
                            echo '<div>Error on template load.</div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Load error on template 'legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Runtime $e) {
                        if ($this->devEnvironment) {
                            echo '<div>Error on run template.</div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Runtime error on template 'legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Syntax $e) {
                        if ($this->devEnvironment) {
                            echo '<div>Error on template syntax.</div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Syntax error on template 'legacy-instant-messaging-panel-body-messenger-user-id-modal.html.twig'.", ['exception' => $e]);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getLegacySitemgrContentListingBeforeModalIncludes method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
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
     * getLegacyInstantMessagingPanelBodyTwig - Render each specific sets of fields to each InstantMessenger integration available to this plugin.
     *
     * @param null $params
     *
     * @param bool $isSponsorPage
     * @throws Exception
     */
    private function getLegacyInstantMessagingPanelBodyTwig(&$params = null, $isSponsorPage = false)
    {
        if (!empty($params) && !empty($this->container)) {
            $messengerData = $params['messenger_data'];
            $telegramData = $params['telegram_data'];
            $whatsappData = $params['whatsapp_data'];
            $highlight = $params['highlight'];
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($messengerData) &&
                    !empty($telegramData) &&
                    !empty($whatsappData)) {
                    /** @var Twig_Environment $twig */
                    $twig = $this->container->get('twig');
                    if ($twig !== null) {
                        $facebookIdHelperEnabled = false;
                        /** @var FacebookHelper $facebookHelper */
                        $facebookHelper = $this->container->get('fb_helper.service');
                        $encodedFacebookInitParams = null;
                        if (($facebookHelper !== null) && $facebookHelper->isFacebookAppEnabled()) {
                            $facebookInitParams = (object)[];
                            $facebookInitParams->appId = $facebookHelper->getAppId();
                            $facebookInitParams->status = true;
                            $facebookInitParams->xfbml = true;
                            $facebookInitParams->version = 'v5.0';
                            $encodedFacebookInitParams = json_encode($facebookInitParams);
                            $facebookIdHelperEnabled = true;
                        }

                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if ($languageHandler !== null) {
                            if ($isSponsorPage) {
                                $multiDomainInfo = $this->container->get('multi_domain.information');
                                if (!empty($multiDomainInfo)) {
                                    $domainLocale = $multiDomainInfo->getLocale();
                                    $lang = $languageHandler->getISOLang($domainLocale);
                                    unset($domainLocale);
                                }
                                unset($multiDomainInfo);
                            } else {
                                $settings = $this->container->get('settings');
                                if (!empty($settings)) {
                                    $sitemgrLocale = $settings->getSetting('sitemgr_language');
                                    $lang = $languageHandler->getISOLang($sitemgrLocale);
                                    unset($sitemgrLocale, $languageHandler);
                                }
                                unset($settings);
                            }
                        }

                        try {
                            echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel-body-messenger-rows.html.twig', ['facebookIdHelperEnabled' => $facebookIdHelperEnabled, 'facebookInitParamsEncodedJson' => $encodedFacebookInitParams, 'messengerData' => $messengerData, 'highlight' => $highlight, 'lang' => $lang]);
                        } catch (Twig_Error_Loader $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Load error on template 'legacy-instant-messaging-panel-body-messenger-rows.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Runtime $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Runtime error on template 'legacy-instant-messaging-panel-body-messenger-rows.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Syntax $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Syntax error on template 'legacy-instant-messaging-panel-body-messenger-rows.html.twig'.", ['exception' => $e]);
                                }
                            }
                        }

                        try {
                            echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel-body-telegram-rows.html.twig', ['telegramData' => $telegramData, 'highlight' => $highlight]);
                        } catch (Twig_Error_Loader $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Load error on template 'legacy-instant-messaging-panel-body-telegram-rows.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Runtime $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Runtime error on template 'legacy-instant-messaging-panel-body-telegram-rows.html.twig'.", ['exception' => $e]);
                                }
                            }
                        } catch (Twig_Error_Syntax $e) {
                            if ($this->devEnvironment) {
                                echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                            } else {
                                if ($logger!==null) {
                                    $logger->error("Syntax error on template 'legacy-instant-messaging-panel-body-telegram-rows.html.twig'.", ['exception' => $e]);
                                }
                            }
                        }

                        $requestStack = $this->container->get('request_stack');
                        if (!empty($requestStack)) {
                            $currentRequest = $requestStack->getCurrentRequest();
                            if (!empty($currentRequest)) {
                                $locale = $currentRequest->getLocale();
                                if (!empty($locale) && strlen($locale) >= 2) {
                                    $locale = substr($locale, 0, 2);
                                    $internationalPhoneCodeJsonFilePath = __DIR__ . '/Resources/assets/InternationalPhoneCode_' . $locale . '.json';
                                    if (!file_exists($internationalPhoneCodeJsonFilePath)) {
                                        $internationalPhoneCodeJsonFilePath = __DIR__ . '/Resources/assets/InternationalPhoneCode_en.json';
                                    }
                                    if (file_exists($internationalPhoneCodeJsonFilePath)) {
                                        $internationalPhoneCodeJsonFileContent = file_get_contents(__DIR__ . '/Resources/assets/InternationalPhoneCode_' . $locale . '.json');
                                        if (!empty($internationalPhoneCodeJsonFileContent)) {
                                            $decodedJsonInternationalPhoneCode = json_decode($internationalPhoneCodeJsonFileContent, false);
                                            if (JSON_ERROR_NONE === json_last_error()) {
                                                if (property_exists($decodedJsonInternationalPhoneCode, 'phone_country_codes') && is_array($decodedJsonInternationalPhoneCode->phone_country_codes)) {
                                                    try {
                                                        echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel-body-whatsapp-rows.html.twig', ['whatsappData' => $whatsappData, 'highlight' => $highlight, 'phone_country_codes' => $decodedJsonInternationalPhoneCode->phone_country_codes]);
                                                    } catch (Twig_Error_Loader $e) {
                                                        if ($this->devEnvironment) {
                                                            echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                                                        } else {
                                                            if ($logger!==null) {
                                                                $logger->error("Load error on template 'legacy-instant-messaging-panel-body-whatsapp-rows.html.twig'.", ['exception' => $e]);
                                                            }
                                                        }
                                                    } catch (Twig_Error_Runtime $e) {
                                                        if ($this->devEnvironment) {
                                                            echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                                                        } else {
                                                            if ($logger!==null) {
                                                                $logger->error("Runtime error on template 'legacy-instant-messaging-panel-body-whatsapp-rows.html.twig'.", ['exception' => $e]);
                                                            }
                                                        }
                                                    } catch (Twig_Error_Syntax $e) {
                                                        if ($this->devEnvironment) {
                                                            echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                                                        } else {
                                                            if ($logger!==null) {
                                                                $logger->error("Syntax error on template 'legacy-instant-messaging-panel-body-whatsapp-rows.html.twig'.", ['exception' => $e]);
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                $e = new Exception(json_last_error_msg(), json_last_error());
                                                if ($logger!==null) {
                                                    $logger->critical('Unexpected json error on getLegacyFormListingInstantMessagingPanelBodyTwig method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                                                } else {
                                                    $notLoggedCriticalException = $e;
                                                }
                                            }
                                            unset($decodedJsonInternationalPhoneCode);
                                        }
                                        unset($internationalPhoneCodeJsonFileContent);
                                    }
                                    unset($internationalPhoneCodeJsonFilePath);
                                }
                                unset($locale);
                            }
                            unset($currentRequest);
                        }
                        unset($requestStack);
                    }
                    unset($twig);
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getLegacyFormListingInstantMessagingPanelBodyTwig method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $messengerData,
                    $telegramData,
                    $whatsappData,
                    $highlight);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getLegacyInstantMessagingPanelFooterTwig - Render the InstantMessaging panel footer.
     *
     * @param null $params
     *
     * @throws Exception
     */
    private function getLegacyInstantMessagingPanelFooterTwig(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $messengerData = $params['messenger_data'];
            $telegramData = $params['telegram_data'];
            $whatsappData = $params['whatsapp_data'];
            $footerData = $params['footerData'];
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                /** @var Twig_Environment $twig */
                $twig = $this->container->get('twig');
                if ($twig !== null && !empty($footerData)) {
                    try {
                        echo $twig->render('@InstantMessengerIntegration/legacy-instant-messaging-panel-footer.html.twig', ['messengerData' => $messengerData, 'telegramData' => $telegramData, 'whatsappData' => $whatsappData, 'footerData' => $footerData]);
                    } catch (Twig_Error_Loader $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Load error on template 'legacy-instant-messaging-panel-footer.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Runtime $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Runtime error on template 'legacy-instant-messaging-panel-footer.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Syntax $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                        } else {
                            if ($logger!==null) {
                                $logger->error("Syntax error on template 'legacy-instant-messaging-panel-footer.html.twig'.", ['exception' => $e]);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger!==null) {
                    $logger->critical('Unexpected error on getLegacyInstantMessagingPanelFooterTwig method of InstantMessengerIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger,
                    $messengerData,
                    $telegramData,
                    $whatsappData,
                    $highlight);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * getLegacySitemgrConmfigurationBasicInformationBeforeMainContentCloseTag - Method that allows include a new modal in basic configuration page on sitemgr area
     *
     * @param $params
     * @throws Exception
     */
    private function getLegacySitemgrConfigurationBasicInformationBeforeMainContentCloseTag($params)
    {
        $this->getLegacySitemgrContentListingBeforeModalIncludes($params);
    }
}
