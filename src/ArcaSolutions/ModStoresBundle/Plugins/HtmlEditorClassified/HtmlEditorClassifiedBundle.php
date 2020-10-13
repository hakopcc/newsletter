<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\HtmlEditorClassified;

use ArcaSolutions\CoreBundle\Helper\CKEditorDataHelper;
use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class HtmlEditorClassifiedBundle extends Bundle
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
                Hooks::Register('legacy-forms-classified_overwrite_longdescription', function (&$params = null) {
                    $this->getLegacyFormsClassifiedOverwriteLongDescription($params, false);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('legacy-forms-classified_overwrite_longdescription', function (&$params = null) {
                    $this->getLegacyFormsClassifiedOverwriteLongDescription($params, true);
                });
                Hooks::Register('classifieddetail_overwrite_longdescription', function (&$params = null) {
                    $this->getClassifiedDetailOverwriteLongDescription($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of HtmlEditorClassifiedBundle.php', ['exception' => $e]);
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
     * @param null $params
     * @param bool $isSponsorPage
     * @throws Exception
     */
    private function getLegacyFormsClassifiedOverwriteLongDescription(&$params = null, $isSponsorPage = false): void
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
                        echo $twig->render('HtmlEditorClassifiedBundle::legacy-sitemgr-form-classified-html-editor-classified-overwrite-long-description.html.twig', ['id' => 'full-description', 'name' => 'detaildesc', 'rows' => 30, 'cols' => 15, 'content' => $content, 'lang' => $lang]);
                    } catch (Twig_Error_Loader $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on template load.<div class="col-sm-6"></div></div>';
                        } else {
                            if (!empty($logger)) {
                                $logger->error("Load error on template 'legacy-sitemgr-form-classified-html-editor-classified-overwrite-long-description.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Runtime $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on run template.<div class="col-sm-6"></div></div>';
                        } else {
                            if (!empty($logger)) {
                                $logger->error("Runtime error on template 'legacy-sitemgr-form-classified-html-editor-classified-overwrite-long-description.html.twig'.", ['exception' => $e]);
                            }
                        }
                    } catch (Twig_Error_Syntax $e) {
                        if ($this->devEnvironment) {
                            echo '<div class="form-group row custom-content-row">Error on template syntax.<div class="col-sm-6"></div></div>';
                        } else {
                            if (!empty($logger)) {
                                $logger->error("Syntax error on template 'legacy-sitemgr-form-classified-html-editor-classified-overwrite-long-description.html.twig'.", ['exception' => $e]);
                            }
                        }
                    }
                }
                unset($twig);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyFormsClassifiedOverwriteLongDescription method of HtmlEditorClassifiedBundle.php', ['exception' => $e]);
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
     * @throws Exception
     */
    private function getClassifiedDetailOverwriteLongDescription(&$params = null): void
    {
        $paramsReturn = true;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $longDescription = $params['item']->getDetailDesc();
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
                    $logger->critical('Unexpected error on getClassifiedDetailOverwriteLongDescription method of HtmlEditorClassifiedBundle.php', ['exception' => $e]);
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
