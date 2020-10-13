<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\HtmlEditorDeal;

use ArcaSolutions\CoreBundle\Helper\CKEditorDataHelper;
use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class HtmlEditorDealBundle extends Bundle
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
                Hooks::Register('dealform_overwrite_longdescription', function (&$params = null) {
                    return $this->getDealFormOverwriteLongDescription($params, false);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('dealform_overwrite_longdescription', function (&$params = null) {
                    return $this->getDealFormOverwriteLongDescription($params, true);
                });
                Hooks::Register('dealdetail_overwrite_longdescription', function (&$params = null) {
                    return $this->getDealDetailOverwriteLongDescription($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of HtmlEditorDealBundle.php', ['exception' => $e]);
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
     * @param bool $isSponsorPage
     * @throws Exception
     */
    private function getDealFormOverwriteLongDescription(&$params = null, $isSponsorPage = false)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $content = '';

                if (isset($params['content'])) {
                    $content = html_entity_decode($params['content']);
                }

                $twig = $this->container->get('twig');
                if (!empty($twig)) {
                    try {
                        $lang = 'en';
                        /**
                         * @var LanguageHandler $languageHandler
                         */
                        $languageHandler = $this->container->get('languagehandler');
                        if (!empty($languageHandler)) {
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
                        echo $twig->render('HtmlEditorDealBundle::legacy-sitemgr-form-deal-html-editor-deal-overwrite-long-description.html.twig', ['id' => 'long_description', 'name' => 'long_description', 'rows' => 30, 'cols' => 15, 'content' => $content, 'lang' => $lang]);
                    } catch (Twig_Error_Loader $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                        } else {
                            if (!empty($logger)) {
                                $logger->error("Load error on template 'legacy-sitemgr-form-deal-html-editor-deal-overwrite-long-description.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Runtime $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                        } else {
                            if (!empty($logger)) {
                                $logger->error("Runtime error on template 'legacy-sitemgr-form-deal-html-editor-deal-overwrite-long-description.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Syntax $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                        } else {
                            if (!empty($logger)) {
                                $logger->error("Syntax error on template 'legacy-sitemgr-form-deal-html-editor-deal-overwrite-long-description.html.twig'.", ['exception' => $e]);
                            }
                        }
                    }
                }
                unset($twig);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getDealFormOverwriteLongDescription method of HtmlEditorDealBundle.php', ['exception' => $e]);
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
    private function getDealDetailOverwriteLongDescription(&$params = null): void
    {
        $paramsReturn = true;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $longDescription = $params['item']->getLongDescription();
                if ($longDescription !== strip_tags($longDescription)) {
                    $ckEditorDataHelper = new CKEditorDataHelper($this->container);
                    $longDescription = $ckEditorDataHelper->cleanupCKEditorGeneratedRawHtmlString($longDescription);
                    //Has any HTML tag
                    echo '<div class="custom-content ckeditor-content">' . $longDescription . '</div>';//This div is necessary to avoid the container affecting the paragraphs elements (that could avoid paragraph break lines)
                } else {
                    $paramsReturn = false;
                }
            } catch (Exception $e) {
                $paramsReturn = false;
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getDealDetailOverwriteLongDescription method of HtmlEditorDealBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                $params['_return'] = $paramsReturn;
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
        unset($paramsReturn);
    }
}
