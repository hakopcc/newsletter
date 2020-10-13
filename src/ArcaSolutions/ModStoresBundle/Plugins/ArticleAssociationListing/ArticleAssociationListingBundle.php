<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing;

use ArcaSolutions\ArticleBundle\Entity\Article;
use ArcaSolutions\ArticleBundle\Repository\ArticleRepository;
use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ListingBundle\Repository\ListingRepository;
use ArcaSolutions\ListingBundle\Services\ListingService;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\Entity\ArticleAssociated;
use ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\Entity\ListingLevelFieldArticles;
use ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\Services\ArticleAssociationService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Exception;
use PDO;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class ArticleAssociationListingBundle extends Bundle
{
    private $devEnvironment = false;

    /**
     * Method just to allow JMSTranslator include know strings that was not used directly by a trans method call or by a trans twig extension
     */
    private function dummyMethodToIncludeTranslatableString(){
        return;
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');
        if ($translator !== null) {
            $translator->trans('articlesCount', array(), 'advertise');
        }
        unset($translator);
    }

    const HEADER_WIDGET_SECTION_NAME = 'header';
    const MAIN_WIDGET_SECTION_NAME = 'main';
    const SIDEBAR_WIDGET_SECTION_NAME = 'sidebar';
    /**
     * Boots the Bundle.
     */
    public function boot(): void
    {

        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $this->devEnvironment = Kernel::ENV_DEV === $this->container->getParameter('kernel.environment');

            if ($this->isSitemgr()) {
                Hooks::Register('legacy-sitemgr-content-listing-template_before_render-main-widget-placeholder', function (&$params = null) {
                    $this->getLegacySitemgrContentListingTemplateBeforeRenderWidgetPlaceholder($this::MAIN_WIDGET_SECTION_NAME,$params);
                });
                /*
                 * Register sitemgr only bundle hooks
                 */
                Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                    $this->getFormPricingAfterAddFields($params);
                });
                Hooks::Register('paymentgateway_after_save_levels', function (&$params = null) {
                    $this->getPaymentGatewayAfterSaveLevels($params);
                });
                Hooks::Register('formlevels_render_fields', function (&$params = null) {
                    $this->getFormLevelsRenderFields($params);
                });
                Hooks::Register('sitemgrlistingtabs_after_render_tabs', function (&$params = null) {
                    $this->getSitemgrListingTabsAfterRenderTabs($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('articlecode_after_save', function (&$params = null) {
                    $this->getArticleCodeAfterSave($params);
                });
                Hooks::Register('classarticle_before_delete', function (&$params = null) {
                    $this->getClassArticleBeforeDelete($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    $this->getSitemgrHeaderAfterRenderMetatags($params);
                });
                Hooks::Register('formarticle_after_render_renewaldate', function (&$params = null) {
                    $this->getFormArticleAfterRenderRenewalDate($params);
                });
                Hooks::Register('listinglevel_construct', function (&$params = null) {
                    $this->getListingLevelConstruct($params);
                });
                Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                    $this->getListingLevelFeatureBeforeReturn($params);
                });


            } else {

                /*
                * Register front only bundle hooks
                */
                Hooks::Register('detailextension_overwrite_activetab', function (&$params = null) {
                    $this->getDetailExtensionOverwriteActiveTab($params);
                });
                Hooks::Register('listinglevel_construct', function (&$params = null) {
                    $this->getListingLevelConstruct($params);
                });
                Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                    $this->getListingLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('listing_before_add_globalvars', function (&$params = null) {
                    $this->getListingBeforeAddGlobalVars($params);
                });
                Hooks::Register('articlecode_after_save', function (&$params = null) {
                    $this->getArticleCodeAfterSave($params);
                });
                Hooks::Register('classarticle_before_delete', function (&$params = null) {
                    $this->getClassArticleBeforeDelete($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('articledetail_after_render_overview', function (&$params = null) {
                    $this->getArticleDetailAfterRenderOverview($params);
                });
                Hooks::Register('formarticle_after_render_renewaldate', function (&$params = null) {
                    $this->getFormArticleAfterRenderRenewalDate($params);
                });
                Hooks::Register('detailextension_after_setlistingwidgettwigname', function (&$params = null) {
                    $this->getDetailExtensionAfterSetListingWidgetTwigName($params);
                });
                Hooks::Register('detailextension_before_settabhascontent', function (&$params = null) {
                    $this->getDetailExtensionBeforeSetTabHasContent($params);
                });
            }
            Hooks::Register('classarticle_on_return_hasrenewaldate', function (&$params = null) {
                $this->getClassArticleOnReturnHasRenewalDate($params);
            });
            Hooks::Register('listingcode_after_save', function (&$params = null) {
                $this->getListingCodeAfterSave($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of ArticleAssociationListingBundle.php', ['exception' => $e]);
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
                    /** @var ArticleAssociationService $articleAssociationSvc */
                    $articleAssociationSvc = $this->container->get('plugin.articleassociation.service');
                    if ($articleAssociationSvc !== null) {
                        if (!empty($listing) && !empty($listing->id)) {
                            $articleAssociationSvc->updateArticleAssociations($listing->id);
                        }
                    }
                    unset($articleAssociationSvc);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingCodeAfterSave method of ArticleAssociationBundle.php', ['exception' => $e]);
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
     * @param null $params
     * @throws Exception
     */
    private function getFormPricingAfterAddFields(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if ($params['type'] == 'listing') {

                    $translation = $this->container->get('translator');

                    $params['levelOptions'][] = [
                        'name' => 'articles',
                        'type' => 'numeric',
                        'title' => $translation->trans('Article Association'),
                        'tip' => $translation->trans('Number of Articles the listing owner is able to associate'),
                        'min' => 0,
                    ];
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getFormPricingAfterAddFields method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getPaymentGatewayAfterSaveLevels(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if ($params['type'] == 'listing' && $params['levelOptionData']['articles']) {

                    $doctrine = $this->container->get('doctrine');
                    $manager = $this->container->get('doctrine')->getManager();

                    foreach ($params['levelOptionData']['articles'] as $level => $field) {

                        $listingLevel = $doctrine->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy([
                            'level' => $level,
                        ]);

                        if ($listingLevel) {
                            $listingLevel->setField($field);
                            $manager->persist($listingLevel);
                        } else {
                            $listingLevel = new ListingLevelFieldArticles();
                            $listingLevel->setLevel($level);
                            $listingLevel->setField($field);
                            $manager->persist($listingLevel);
                        }
                    }

                    $manager->flush();
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getPaymentGatewayAfterSaveLevels method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getFormLevelsRenderFields(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (is_a($params['levelObj'], 'ListingLevel') && $params['option']['name'] == 'articles') {

                    $params['levelObj']->articles = [];

                    $resultLevel = $this->container->get('doctrine')->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findBy([],
                        ['level' => 'DESC']);

                    if ($resultLevel) {
                        foreach ($resultLevel as $levelfield) {
                            $params['levelObj']->articles[] = $levelfield->getField();
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getFormLevelsRenderFields method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getSitemgrListingTabsAfterRenderTabs(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $translation = $this->container->get('translator');

                $resultLevel = $this->container->get('doctrine')->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy([
                    'level' => $params['listing']->getNumber('level'),
                ]);

                if (!empty($resultLevel) && ARTICLE_FEATURE == 'on' && CUSTOM_ARTICLE_FEATURE == 'on' && $resultLevel->getField() > 0) {
                    printf('<li %s><a href="%s/article.php?id=%d" role="tab">%s</a></li>',
                        $params['activeTab']['article'],
                        $params['url_redirect'],
                        $params['id'],
                        ucfirst($translation->trans('Article'))
                    );
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getSitemgrListingTabsAfterRenderTabs method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getModulesFooterAfterRenderJs(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (string_strpos($_SERVER['PHP_SELF'], 'content/' . ARTICLE_FEATURE_FOLDER . '/') !== false ||
                    string_strpos($_SERVER['PHP_SELF'], 'sponsors/' . ARTICLE_FEATURE_FOLDER . '/') !== false) {

                    $request = $this->container->get('request_stack')->getCurrentRequest();
                    if ($request !== null) {
                        $attached_listing = $request->get('listing_id', 0);
                    }

                    if (empty($attached_listing) && !empty($params['id'])) {
                        $manager = $this->container->get('doctrine')->getManager();
                        $connection = $manager->getConnection();

                        $statement = $connection->prepare('SELECT listing_id FROM ArticleAssociated WHERE article_id = :article_id LIMIT 1');
                        $statement->bindValue('article_id', $params['id']);
                        $statement->execute();

                        $attached_listing = $statement->fetch()['listing_id'];
                    }

                    echo $this->container->get('twig')->render('ArticleAssociationListingBundle::js/article_form_association.html.twig',
                        [
                            'members' => $params['members'],
                            'attached_listing' => $attached_listing,
                            'id' => $params['id']
                        ]);

                }

                if (string_strpos($_SERVER['PHP_SELF'], 'content/' . LISTING_FEATURE_FOLDER . '/article') !== false) {

                    $listing = null;
                    $attached_article = [];

                    if (isset($params['id'])) {

                        /** @var DoctrineRegistry $doctrine */
                        $doctrine = $this->container->get('doctrine');
                        /** @var Listing $listing */
                        $listing = $doctrine->getRepository('ListingBundle:Listing')->find($params['id']);

                        /** @var ArticleAssociated[] $selectedListingArticles */
                        $selectedListingArticles = $doctrine->getRepository('ArticleAssociationListingBundle:ArticleAssociated')->findBy(['listingId' => $params['id']]);
                        $attached_article = array_reduce($selectedListingArticles, function ($carry, $item) {
                            if ($item instanceof ArticleAssociated) {
                                /** @var ArticleAssociated $item */
                                if (empty($carry)) {
                                    $carry = array();
                                }
                                /** @var Article $article */
                                $article = $item->getArticle();
                                if ($article !== null && !in_array($article->getId(), $carry, true)) {
                                    $carry[] = $article->getId();
                                }
                            }
                            return $carry;
                        });

                        if ($attached_article === null) {
                            $attached_article = array();
                        }

                        $associationLevel = $doctrine->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy(['level' => $listing->getLevel()]);
                    }

                    echo $this->container->get('twig')->render('ArticleAssociationListingBundle::js/listing_form_association.html.twig',
                        [
                            'members' => $params['members'],
                            'listing' => $listing,
                            'level' => $associationLevel,
                            'attached_article' => str_replace('"', '\"', json_encode($attached_article))
                        ]);

                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getModulesFooterAfterRenderJs method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getArticleCodeAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $listing_attached = (isset($_POST['listing_id']) && !empty($_POST['listing_id'])) ? $_POST['listing_id'] : null;

                $statement = $connection->prepare('SELECT listing_id FROM ArticleAssociated WHERE article_id = :id');
                $statement->bindValue('id', $params['article']->getNumber('id'));
                $statement->execute();

                $results = $statement->fetch();

                $statement = null;
                if ($results) {
                    if ($listing_attached !== null) {
                        $statement = $connection->prepare('UPDATE ArticleAssociated SET listing_id = :listingId WHERE article_id = :id');
                    } else {
                        $statement = $connection->prepare('DELETE FROM ArticleAssociated WHERE article_id = :id AND listing_id = :listingId');
                    }
                } else {
                    if ($listing_attached !== null) {
                        $statement = $connection->prepare('INSERT INTO ArticleAssociated (article_id, listing_id) VALUES (:id, :listingId)');
                    }
                }

                if ($statement !== null) {
                    $statement->bindValue('id', $params['article']->getNumber('id'));
                    $statement->bindValue('listingId', ($listing_attached !== null) ? $listing_attached : $results['listing_id']);
                    $statement->execute();
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getArticleCodeAfterSave method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getClassArticleBeforeDelete(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $statement = $connection->prepare('DELETE FROM ArticleAssociated WHERE article_id = :id');
                $statement->bindValue('id', $params['that']->id);
                $statement->execute();
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getClassArticleBeforeDelete method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getClassListingBeforeDelete(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $statement = $connection->prepare('DELETE FROM ArticleAssociated WHERE listing_id = :id');
                $statement->bindValue('id', $params['that']->id);
                $statement->execute();
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getClassListingBeforeDelete method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getSitemgrHeaderAfterRenderMetatags(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                echo '<style>
            #listingSelectBox .selectize-input{
                max-height: 34px;
            }
        </style>';
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getSitemgrHeaderAfterRenderMetatags method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getFormArticleAfterRenderRenewalDate(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $listing_id = null;
                $statement = null;
                if (!empty($params['id'])) {
                    $statement = $connection->prepare('SELECT listing_id FROM ArticleAssociated WHERE article_id = :article_id LIMIT 1');
                    $statement->bindValue('article_id', $params['id']);
                    $statement->execute();
                    $listing_id = $statement->fetch()['listing_id'];

                    if (empty($listing_id)) {
                        $listing_id = null;
                    }
                }

                echo $this->container->get('twig')->render('ArticleAssociationListingBundle::form-sitemgr-article.html.twig', [
                    'listing_id' => $listing_id
                ]);
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getFormArticleAfterRenderRenewalDate method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getDetailExtensionOverwriteActiveTab(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $statement = $connection->prepare('SELECT id FROM ArticleAssociated WHERE listing_id = :listing_id LIMIT 1');
                $statement->bindValue('listing_id', $params['listing']->getId());
                $statement->execute();

                $associationId = $statement->fetch(PDO::FETCH_COLUMN);

                $resultLevel = $this->container->get('doctrine')->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy([
                    'level' => $params['listing']->getLevel(),
                ]);

                !empty($resultLevel) and $num_articles_allowed = $resultLevel->getField();

                if (!empty($associationId) && !empty($num_articles_allowed)) {
                    $params['contentCount']++;
                    $params['activeTab'] = $params['activeTab'] < 7 ? 7 : $params['activeTab'];
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getDetailExtensionOverwriteActiveTab method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getListingLevelConstruct(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $params['that']->articlesCount = 0;
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingLevelConstruct method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getListingLevelFeatureBeforeReturn(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $resultLevel = $this->container->get('doctrine')->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy([
                    'level' => $params['level']->getValue(),
                ]);

                if (!empty($resultLevel)) {
                    $params['listingLevel']->articlesCount = $resultLevel->getField();
                } else {
                    $params['listingLevel']->articlesCount = 0;
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingLevelFeatureBeforeReturn method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getListingBeforeAddGlobalVars(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $articles = null;

                $resultLevel = $this->container->get('doctrine')->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy([
                    'level' => $params['item']->getLevel(),
                ]);

                !empty($resultLevel) and $num_articles_allowed = $resultLevel->getField();

                if (!empty($num_articles_allowed)) {
                    $statement = $connection->prepare('SELECT id FROM Article WHERE id IN (SELECT article_id FROM ArticleAssociated WHERE listing_id = :listing_id) AND status = :status ORDER BY id LIMIT :limit');
                    $statement->bindValue('listing_id', $params['item']->getId());
                    $statement->bindValue('status', 'A');
                    $statement->bindValue('limit', (int)$num_articles_allowed, PDO::PARAM_INT);
                    $statement->execute();

                    $resArticles = $statement->fetchAll();

                    foreach ($resArticles as $article) {
                        $articles[] = $this->container->get('doctrine')->getRepository('ArticleBundle:Article')->find($article['id']);
                    }

                    $this->container->get('twig')->addGlobal('articlesAssoc', $articles);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingBeforeAddGlobalVars method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getArticleDetailAfterRenderOverview(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $statement = $connection->prepare('SELECT listing_id FROM ArticleAssociated WHERE article_id = :article_id LIMIT 1');
                $statement->bindValue('article_id', $params['item']->getId());
                $statement->execute();

                $listingId = $statement->fetch(PDO::FETCH_COLUMN);

                if (!empty($listingId)) {

                    $listing = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->findOneBy([
                        'id' => $listingId,
                        'status' => 'A',
                    ]);

                    if ($listing) {

                        $resultLevel = $this->container->get('doctrine')->getRepository('ArticleAssociationListingBundle:ListingLevelFieldArticles')->findOneBy([
                            'level' => $listing->getLevel(),
                        ]);

                        if (!empty($resultLevel) && $resultLevel->getField()) {

                            $listingItemDetail = new ListingItemDetail($this->container, $listing);
                            $level = $listingItemDetail->getLevel();

                            $locations = $this->container->get('location.service')->getLocations($listing);
                            $locations_ids = [];
                            $locations_rows = [];
                            foreach (array_filter($locations) as $levelLocation => $location) {
                                $key = substr($levelLocation, 0, 2) . ':' . $location->getId();
                                $locations_ids[] = $key;
                                $locations_rows[$key] = $location;
                            }

                            echo $this->container->get('twig')->render('ArticleAssociationListingBundle::articleassoc-articledetail.html.twig',
                                [
                                    'listing' => $listing,
                                    'level' => $level,
                                    'locationsIDs' => $locations_ids,
                                    'locationsObjs' => $locations_rows,
                                ]);

                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getArticleDetailAfterRenderOverview method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    /**
     * @param string $widgetSectionName
     * @param null $params
     * @throws Exception
     */
    private function getLegacySitemgrContentListingTemplateBeforeRenderWidgetPlaceholder($widgetSectionName, &$params = null): void
    {
        $widgetSectionValidNames = array($this::HEADER_WIDGET_SECTION_NAME,$this::MAIN_WIDGET_SECTION_NAME,$this::SIDEBAR_WIDGET_SECTION_NAME);
        if (!empty($params) && !empty($widgetSectionName) && in_array($widgetSectionName, $widgetSectionValidNames, true) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $imagePathRef = &$params['image_path'];
                $listingWidgetObject = $params['listing_widget'];
                if (!empty($listingWidgetObject)) {
                    if ($listingWidgetObject instanceof ListingTemplateListingWidget) {
                        /** @var ListingTemplateListingWidget $listingWidgetObject */
                        $listingWidget = $listingWidgetObject->getListingWidget();
                        if ($listingWidget !== null && $listingWidget->getTitle() === ListingWidgetAssociatedArticle::LISTING_WIDGET_TITLE) {
                            if($this->container->hasParameter('alias_sitemgr_module')) {
                                $siteManagerAlias = $this->container->getParameter('alias_sitemgr_module');
                                /** @var KernelInterface $kernel */
                                $kernel = $this->container->get('kernel');
                                if ($kernel !== null && !empty($siteManagerAlias)) {
                                    $kernelRootDir = $kernel->getRootDir();
                                    if (!empty($kernelRootDir)) {
                                        /** @var RequestStack $requestStackFromContainer */
                                        $requestStack = $this->container->get('request_stack');
                                        if($requestStack!==null) {
                                            /** @var Request $currentRequest */
                                            $currentRequest = $requestStack->getCurrentRequest();
                                            if($currentRequest!==null) {
                                                $schemeAndHttpHost = $currentRequest->getSchemeAndHttpHost();
                                                if(!empty($schemeAndHttpHost)) {
                                                    $placeHolderFileName = 'associated-articles.jpg';
                                                    $listingWidgetImagesPathFromSiteMgr = 'assets/img/listing-widget-placeholder/' . $widgetSectionName;
                                                    $physicalListingWidgetImagesPath = $kernelRootDir . '/../web/' . $siteManagerAlias . '/' . $listingWidgetImagesPathFromSiteMgr;
                                                    $httpListingWidgetImagesPath = $schemeAndHttpHost . '/' . $siteManagerAlias . '/' . $listingWidgetImagesPathFromSiteMgr;
                                                    if (file_exists($physicalListingWidgetImagesPath . '/' . $placeHolderFileName)) {
                                                        $imagePathRef = $httpListingWidgetImagesPath. '/' . $placeHolderFileName;
                                                    }
                                                    unset($placeHolderFileName,
                                                        $listingWidgetImagesPathFromSiteMgr,
                                                        $physicalListingWidgetImagesPath,
                                                        $httpListingWidgetImagesPath);
                                                }
                                                unset($schemeAndHttpHost);
                                            }
                                            unset($currentRequest);
                                        }
                                        unset($requestStack);
                                    }
                                    unset($kernelRootDir);
                                }
                                unset($kernel, $siteManagerAlias);
                            }
                        }
                        unset($listingWidget);
                    }
                }
                unset($listingWidgetObject);
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getLegacySitemgrContentListingTemplateBeforeRenderWidgetPlaceholder method of ArticleAssociationListingBundle.php', ['exception' => $e]);
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
        unset($widgetSectionValidNames);
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getDetailExtensionAfterSetListingWidgetTwigName(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $listingTemplateListingWidget = &$params['listingtemplate_listingwidget'];
                $listingWidgetSection = $params['listingwidget_section'];
                $listingWidgetTwigNameRef = &$params['listingwidget_twigname'];
                $listingWidgetSectionTwigName = $params['listingwidget_section_twigname'];
                if (!empty($listingTemplateListingWidget) &&
                    !empty($listingWidgetSectionTwigName) &&
                    !empty($listingWidgetSection) &&
                    !empty($listingWidgetTwigNameRef)) {


                    /** @var Twig_Environment $twig */
                    $twig = $this->container->get('twig');
                    if ($twig !== null) {
                        $twigLoader = $twig->getLoader();
                        if ($twigLoader !== null){
                            if($twigLoader->exists('ArticleAssociationListingBundle' . $listingWidgetTwigNameRef)) {//if exists and is independent of the section
                                $listingWidgetTwigNameRef = 'ArticleAssociationListingBundle' . $listingWidgetTwigNameRef;
                            } elseif ($twigLoader->exists('ArticleAssociationListingBundle' . $listingWidgetSectionTwigName)) {//if exists and is dependent of the section
                                $listingWidgetTwigNameRef = 'ArticleAssociationListingBundle' . $listingWidgetSectionTwigName;
                            }
                        }
                        unset($twigLoader);
                    }
                    unset($twig);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getDetailExtensionAfterSetListingWidgetFilePath method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($listingTemplateListingWidget, $listingWidgetSection, $logger, $twig, $twigLoader);
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
    private function getDetailExtensionBeforeSetTabHasContent(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $hasContentRef = &$params['has_content'];
                /**
                 * @var ListingTemplateTab $tab
                 */
                $tab = $params['tab'];
                $listing = $params['listing'];
                $listingLevel = $params['listing_level_features'];
                $tabSectionWidgets = $params['tab_section_widgets'];
                if ($hasContentRef !== null && !empty($listing) && !empty($listingLevel) && !empty($tab) && !empty($tabSectionWidgets)) {
                    $tabHasArticleAssociationWidget = false;
                    foreach ($tabSectionWidgets as $sectionWidgets) {
                        /** @var ListingTemplateListingWidget $listingTemplateListingWidget */
                        foreach ($sectionWidgets as $listingTemplateListingWidget) {
                            $listingWidget = $listingTemplateListingWidget->getListingWidget();
                            if ($listingWidget !== null && $listingWidget->getTitle() === ListingWidgetAssociatedArticle::LISTING_WIDGET_TITLE) {
                                $tabHasArticleAssociationWidget = true;
                            }
                            unset($listingWidget);
                        }
                    }
                    if ($tabHasArticleAssociationWidget) {
                        $willRender = false;
                        if (property_exists($listingLevel, 'articlesCount') && !empty($listingLevel->articlesCount)) {
                            /**
                             * @var DoctrineRegistry $doctrine
                             */
                            $doctrine = $this->container->get("doctrine");
                            if ($doctrine !== null) {
                                /** @var ObjectManager $manager */
                                $manager = $doctrine->getManager();
                                /** @var Connection $connection */
                                $connection = $manager->getConnection();

                                $statement = $connection->prepare('SELECT id FROM Article WHERE id IN (SELECT article_id FROM ArticleAssociated WHERE listing_id = :listing_id) AND status = :status ORDER BY id LIMIT :limit');
                                $statement->bindValue('listing_id', $listing->getId());
                                $statement->bindValue('status', 'A');
                                $statement->bindValue('limit', (int)$listingLevel->articlesCount, PDO::PARAM_INT);
                                $statement->execute();
                                $resArticlesRowCount = $statement->rowCount();
                                $willRender = !empty($resArticlesRowCount);
                                unset($resArticlesRowCount, $statement, $connection, $manager);
                            }
                            unset($doctrine);
                        }
                        $hasContentRef = ($hasContentRef || $willRender);
                        unset($willRender);
                    }
                    unset($tabHasArticleAssociationWidget);
                }
                unset($tab, $listing, $listingLevel, $tabSectionWidgets);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getDetailExtensionBeforeSetTabHasContent method of ArticleAssociationListingBundle.php', ['exception' => $e]);
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
     * @param $params
     * @throws Exception
     */
    private function getClassArticleOnReturnHasRenewalDate($params): void
    {
        if (!empty($params) && !empty($this->container)) {
            $returnValue = false;
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $classArticleObjRef = &$params['that'];
                if (property_exists($classArticleObjRef, 'id') && !empty($classArticleObjRef->id)) {
                    /**
                     * @var DoctrineRegistry $doctrine
                     */
                    $doctrine = $this->container->get("doctrine");
                    /** @var ListingService $listingService */
                    $listingService = $this->container->get("listing.service");
                    if ($doctrine !== null && $listingService !== null) {
                        /** @var ListingRepository $listingRepository */
                        $listingRepository = $doctrine->getRepository('ListingBundle:Listing');
                        /** @var ArticleRepository $articleRepository */
                        $articleRepository = $doctrine->getRepository('ArticleBundle:Article');
                        /** @var EntityRepository $articleAssociatedRepository */
                        $articleAssociatedRepository = $doctrine->getRepository('ArticleAssociationListingBundle:ArticleAssociated');
                        if ($articleAssociatedRepository !== null && $articleRepository !== null && $listingRepository !== null) {
                            /** @var Article $article */
                            $article = $articleRepository->find($classArticleObjRef->id);
                            if ($article !== null) {
                                /** @var ArticleAssociated $articleAssociated */
                                $articleAssociated = $articleAssociatedRepository->findOneBy(['article' => $article]);
                                if ($articleAssociated !== null) {
                                    $listingId = $articleAssociated->getListingId();
                                    if (!empty($listingId)) {
                                        /** @var Listing $listing */
                                        $listing = $listingRepository->find($listingId);
                                        if ($listing !== null) {
                                            $listingNeedToCheckOut = $listingService->needToCheckOut($listing);
                                            if ($listingNeedToCheckOut !== null && !$listingNeedToCheckOut) {
                                                $returnValue = true;
                                            }
                                            unset($listingNeedToCheckOut);
                                        }
                                        unset($listing);
                                    }
                                    unset($listingId);
                                }
                                unset($articleAssociated);
                            }
                            unset($article);
                        }
                        unset($listingRepository, $articleAssociatedRepository, $articleRepository);
                    }
                    unset($listingService, $doctrine);
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getClassArticleOnReturnHasRenewalDate method of ArticleAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    throw $notLoggedCriticalException;
                }
                $params['_return'] = $returnValue;
            }
        }
    }
}
