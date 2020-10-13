<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ImageBundle\Entity\Gallery;
use ArcaSolutions\ImageBundle\Entity\GalleryImage;
use ArcaSolutions\ImageBundle\Entity\GalleryItem;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\DefaultListingLevelFields;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\DefaultListingTemplateFields;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\RatingType;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Repository\RatingTypeRepository;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Expr;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class AdvancedReviewListingBundle extends Bundle
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
            $translator->trans('hasAdvancedReview', array(), 'advertise');
        }
        unset($translator);
    }

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
                Hooks::Register('legacy_contentlistingtemplate_listingtemplate_after_render_category', function (&$params = null) {
                    return $this->getLegacyContentListingtemplateAfterRenderCategory($params);
                });
                Hooks::Register('wysiwyg_listingtemplatelistingwidgetservice_after_save', function (&$params = null) {
                    return $this->getWysiwygListingtemplatelistingwidgetserviceAfterSave($params);
                });
                Hooks::Register('colorschemefunct_before_write_customcss', function (&$params = null) {
                    return $this->getColorSchemeFunctBeforeWriteCustomCss($params);
                });
                Hooks::Register('classreview_before_delete', function (&$params = null) {
                    return $this->getClassReviewBeforeDelete($params);
                });
                Hooks::Register('legacy_contentlistingtemplate_index_before_delete', function (&$params = null) {
                    return $this->getLegacyContentlistingtemplateBeforeDelete($params);
                });
                Hooks::Register('sitemgrfooter_after_render_js', function (&$params = null) {
                    return $this->getSitemgrFooterAfterRenderJs($params);
                });
                Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                    return $this->getFormPricingAfterAddFields($params);
                });
                Hooks::Register('systemfunct_after_setup_availableformfields', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupAvailableFormFields($params);
                });
                Hooks::Register('formreview_after_render_comment', function (&$params = null) {
                    return $this->getFormReviewAfterRenderComment($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    return $this->getSitemgrHeaderAfterRenderMetatags($params);
                });

            } else {

                /*
                * Register front only bundle hooks
                */
                Hooks::Register('views-blocks-utility-detail-reviewstarsmacro_ovewrite_ratestars', function (&$params = null) {
                    return $this->getListingDetailOverwriteRateStars($params);
                });
                Hooks::Register('modalwritereview_overwrite_ratestars', function (&$params = null) {
                    return $this->getModalWriteReviewOverwriteRateStars($params);
                });
                Hooks::Register('search_before_render', function (&$params = null) {
                    return $this->getSearchBeforeRender($params);
                });
                Hooks::Register('reviewtype_after_buildform', function (&$params = null) {
                    return $this->getReviewTypeAfterBuildForm($params);
                });
                Hooks::Register('classreview_before_delete', function (&$params = null) {
                    return $this->getClassReviewBeforeDelete($params);
                });
                Hooks::Register('reviewhandler_before_returnsave', function (&$params = null) {
                    return $this->getReviewHandlerBeforeReturnSave($params);
                });
                Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                    return $this->getListingLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('systemfunct_after_setup_availableformfields', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupAvailableFormFields($params);
                });
                Hooks::Register('listinglevel_construct', function (&$params = null) {
                    return $this->getListingLevelConstruct($params);
                });
                Hooks::Register('modalwritereview_after_render_fields', function (&$params = null) {
                    return $this->getModalWriteReviewAfterRenderFields($params);
                });
                Hooks::Register('base_before_render_styles', function (&$params = null) {
                    return $this->getBaseBeforeRenderStyles($params);
                });
                Hooks::Register('reviewdetail_after_review', function (&$params = null) {
                    return $this->getReviewDetailAfterReview($params);
                });
                Hooks::Register('listingdetail_overwrite_reviewjs', function (&$params = null) {
                    return $this->getListingDetailOverwriteReviewJs($params);
                });
            }
            Hooks::Register('listingtfieldservice-createdefaultlistingtemplatefields_after_setstandardinsertsarray', function (&$params = null) {
                return $this->getListingTFieldServiceCreateDefaultListingTemplateFieldsAfterSetStandardInsertsArray($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of AdvancedReviewListingBundle.php', ['exception' => $e]);
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
    private function getListingTFieldServiceCreateDefaultListingTemplateFieldsAfterSetStandardInsertsArray(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $standardInsertsArrayRef = &$params['standard_to_insert_fields_array'];
                if(is_array($standardInsertsArrayRef)) {
                    $standardInserts = array(
                        array(
                            'fieldType' => ListingTField::DEFAULT_TYPE,
                            'field'     => DefaultListingTemplateFields::ADVANCED_REVIEW
                        )
                    );
                    foreach ($standardInserts as $standardInsert) {
                        $standardInsertsArrayRef[] = $standardInsert;
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getListingTFieldServiceCreateDefaultListingTemplateFieldsAfterSetStandardInsertsArray method of AdvancedReviewListingBundle.php', ['exception' => $e]);
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
    private function getLegacyContentListingtemplateAfterRenderCategory(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $doctrine = $this->container->get('doctrine');
                if (!empty($doctrine)) {
                    $ratingTypeRepository = $doctrine->getRepository('AdvancedReviewListingBundle:RatingType');
                    if (!empty($ratingTypeRepository)) {
                        $ratingTypes = [];
                        /**
                         * @var ListingTemplate $listingTemplate
                         */
                        $listingTemplate = $params['listing_template'];
                        if ($listingTemplate !== null) {
                            $listingTemplateId = $listingTemplate->getId();
                            if (!empty($listingTemplateId)) {
                                $ratingTypes = $ratingTypeRepository->findBy(['listingTemplateId' => $listingTemplateId]);
                            }
                            unset($listingTemplateId);
                        }
                        unset($listingTemplate);

                        $totalItems = $this->container->getParameter('advanced_review_listing.total_reviews_options');

                        echo $this->container->get('twig')->render('AdvancedReviewListingBundle::form-rating-types.html.twig', [
                            'ratingTypes' => $ratingTypes,
                            'totalItems' => $totalItems,
                        ]);
                        unset($ratingTypes, $totalItems);
                    }
                    unset($ratingTypeRepository);
                }
                unset($doctrine);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyContentListingtemplateAfterRenderCategory method of AdvancedReviewListingBundle.php', ['exception' => $e]);
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
    private function getWysiwygListingtemplatelistingwidgetserviceAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $httpPostArrayRef = &$params['http_post_array'];
                $listingTemplateId = $params['listing_template_id'];
                $listintTemplateSuccessfulySaved = $params['successfully_saved'];

                if (!empty($httpPostArrayRef) && is_array($httpPostArrayRef) && $listintTemplateSuccessfulySaved) {
                    $doctrine = $this->container->get('doctrine');
                    if (!empty($doctrine)) {
                        /** @var ObjectManager $manager */
                        $manager = $doctrine->getManager();
                        if ($manager !== null) {
                            /** @var RatingTypeRepository $ratingTypeRepository */
                            $ratingTypeRepository = $doctrine->getRepository('AdvancedReviewListingBundle:RatingType');
                            if ($ratingTypeRepository !== null) {
                                $flushNeeded = false;
                                $preExistentRatingTypesFromHttpPost = array();
                                if (isset($httpPostArrayRef['rating']['old']) && is_array($httpPostArrayRef['rating']['old'])) {
                                    $preExistentRatingTypesFromHttpPost = $httpPostArrayRef['rating']['old'];
                                }
                                $newRatingTypesFromHttpPost = array();
                                if (isset($httpPostArrayRef['rating']['new']) && is_array($httpPostArrayRef['rating']['new'])) {
                                    $newRatingTypesFromHttpPost = $httpPostArrayRef['rating']['new'];
                                }
                                if (!empty($listingTemplateId) && $listingTemplateId > 0) {
                                    /** @var RatingType[] $listingTemplateRatings */
                                    $listingTemplateRatings = $ratingTypeRepository->findBy([
                                        'listingTemplateId' => $listingTemplateId
                                    ]);
                                    foreach ($listingTemplateRatings as $listingTemplateRating) {
                                        $listingTemplateRatingId = $listingTemplateRating->getId();
                                        if (!array_key_exists($listingTemplateRatingId, $preExistentRatingTypesFromHttpPost)) {
                                            $manager->remove($listingTemplateRating);
                                            if (!$flushNeeded) {
                                                $flushNeeded = true;
                                            }
                                        }
                                        unset($listingTemplateRatingId);
                                    }
                                }
                                if (!empty($preExistentRatingTypesFromHttpPost)) {
                                    foreach ($preExistentRatingTypesFromHttpPost as $id => $label) {
                                        /** @var RatingType $ratingType */
                                        $ratingType = $ratingTypeRepository->find($id);
                                        $label = trim($label);
                                        if (!empty($label)) {
                                            $ratingType->setLabel($label);
                                            $manager->persist($ratingType);
                                        } else {
                                            $manager->remove($ratingType);
                                        }
                                        if (!$flushNeeded) {
                                            $flushNeeded = true;
                                        }
                                    }
                                }
                                if (!empty($newRatingTypesFromHttpPost)) {
                                    foreach ($newRatingTypesFromHttpPost as $label) {
                                        $label = trim($label);
                                        if (!empty($label)) {
                                            $ratingType = new RatingType();
                                            $ratingType->setLabel($label);
                                            $ratingType->setListingTemplateId($listingTemplateId);
                                            $manager->persist($ratingType);
                                            if (!$flushNeeded) {
                                                $flushNeeded = true;
                                            }
                                        }
                                    }
                                }
                                if ($flushNeeded) {
                                    $manager->flush();
                                }
                            }
                        }
                        unset($manager);
                    }
                    unset($doctrine);
                }
                unset($listingTemplateId, $listintTemplateSuccessfulySaved);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getWysiwygListingtemplatelistingwidgetserviceAfterSave method of AdvancedReviewListingBundle.php', ['exception' => $e]);
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
    private function getColorSchemeFunctBeforeWriteCustomCss(&$params = null)
    {
        $params['phpContent'] .= '
            .advanced-rating .advanced-select-rating > span:after {
                color: #' . $params['colors']['color2'] . '
            }';
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyContentlistingtemplateBeforeDelete(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $httpPostArrayRef = &$params['http_post_array'];
                $listingTemplateId = $params['listing_template_id'];
                if (!empty($listingTemplateId)) {
                    $doctrine = $this->container->get('doctrine');
                    if (!empty($doctrine)) {
                        $manager = $doctrine->getManager();
                        if (!empty($manager)) {
                            $connection = $manager->getConnection();
                            if (!empty($connection)) {
                                $ratingTypeRepository = $doctrine->getRepository('AdvancedReviewListingBundle:RatingType');
                                if (!empty($ratingTypeRepository)) {
                                    /**
                                     * @var RatingType[] $ratingTypes
                                     */
                                    $ratingTypes = $ratingTypeRepository->findBy(['listingTemplateId' => $listingTemplateId]);
                                    if (!empty($ratingTypes)) {
                                        $flushNeeded = false;
                                        foreach ($ratingTypes as $ratingType) {
                                            $statement = $connection->prepare('DELETE FROM Review_RatingType WHERE rating_id =' . $ratingType->getId());//TODO: Check if this is necessary, due to DELETE CASCADE present in the Entity
                                            $statement->execute();
                                            $manager->remove($ratingType);
                                            if (!$flushNeeded) {
                                                $flushNeeded = true;
                                            }
                                        }
                                        if ($flushNeeded) {
                                            $manager->flush();
                                        }
                                    }
                                    unset($ratingTypes);
                                }
                                unset($ratingTypeRepository);
                            }
                            unset($connection);
                        }
                        unset($manager);
                    }
                    unset($doctrine);
                }
                unset($listingTemplateId);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyContentlistingtemplateBeforeDelete method of AdvancedReviewListingBundle.php', ['exception' => $e]);
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
    private function getClassReviewBeforeDelete(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $that = $params['that'];
                if (!empty($that)) {
                    $doctrine = $this->container->get('doctrine');
                    if (!empty($doctrine)) {
                        $manager = $doctrine->getManager();
                        if (!empty($manager)) {
                            $connection = $manager->getConnection();
                            if (!empty($connection)) {

                                $statement = $connection->prepare('DELETE FROM Review_RatingType WHERE review_id = :id');
                                $statement->bindValue('id', $that->id);
                                $statement->execute();
                            }
                            unset($connection);
                        }
                        unset($manager);
                    }
                    unset($doctrine);
                }
                unset($that);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getClassReviewBeforeDelete method of AdvancedReviewListingBundle.php', ['exception' => $e]);
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
    private function getSitemgrFooterAfterRenderJs(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], 'content/listing/template/listing-template.php') !== false) {
            echo $this->container->get('twig')->render('AdvancedReviewListingBundle::js/sitemgr-rating-types-js.html.twig');
        }

        if (string_strpos($_SERVER['PHP_SELF'], 'activity/reviews-comments') !== false) {
            echo $this->container->get('twig')->render('AdvancedReviewListingBundle::js/sitemgr-form-review-js.html.twig');
        }
    }

    /**
     * @param null $params
     */
    private function getFormPricingAfterAddFields(&$params = null)
    {
        if ($params['type'] === 'listing') {

            $params['levelOptions'][] = [
                'name' => DefaultListingLevelFields::ADVANCED_REVIEW,
                'type' => 'checkbox',
                'title' => $this->container->get('translator')->trans('Advanced Review'),
                'tip' => $this->container->get('translator')->trans('Enable Advanced Review options to the listings?'),
            ];

        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getSystemFunctAfterSetupAvailableFormFields(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $params['avFields'][DefaultListingTemplateFields::ADVANCED_REVIEW] = DefaultListingLevelFields::ADVANCED_REVIEW;
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getSystemFunctAfterSetupAvailableFormFields method of AdvancedReviewListingBundle.php', ['exception' => $e]);
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
    private function getFormReviewAfterRenderComment(&$params = null)
    {
        $this->container->get('utility')->setPackages();

        $gallery = $this->container->get('doctrine')->getEntityManager()->createQueryBuilder()
            ->select('gimg')
            ->from('ImageBundle:GalleryImage', 'gimg')
            ->leftJoin('ImageBundle:GalleryItem', 'gitem', Expr\Join::WITH, 'gitem.galleryId = gimg.galleryId')
            ->where('gitem.itemType = :module')
            ->andWhere('gitem.itemId = :id')
            ->orderBy('gimg.imageDefault', 'DESC')
            ->setMaxResults($this->container->getParameter('advanced_review_listing.total_review_images'))
            ->setParameter('module', 'review')
            ->setParameter('id', $params['id'])
            ->getQuery()
            ->getResult();

        echo $this->container->get('twig')->render('AdvancedReviewListingBundle::form-review-gallery.html.twig', [
            'gallery' => $gallery,
        ]);
    }

    /**
     * @param null $params
     */
    private function getSitemgrHeaderAfterRenderMetatags(&$params = null)
    {
        echo '<style>
            .review-gallery .row {
                border: solid thin #E3E3E1;
                padding: 10px;
                margin: 0 0 10px 0;
            }
            .review-gallery .row  > div {
                padding: 0;
            }
            .review-gallery .btn-delete {
                margin-top: 5%;
                padding: 5px 13px
            }
            .review-gallery img {
			    object-fit: cover;
            }
        </style>';
    }

    /**
     * @param null $params
     */
    private function getListingDetailOverwriteRateStars(&$params = null)
    {
        $showYelpReview = $this->container->get('modstore.storage.service')->retrieve('showYelpReview');

        if (!empty($showYelpReview) && $showYelpReview === 'true') {
            $params['_return'] = false;
        } else {
            $ratings = [];
            $ratingTypes = [];
            /**
             * @var Listing $listingItem
             */
            $listingItem = $params['item'];
            /**
             * @var ListingTemplate $listingItemTemplate
             */
            $listingItemTemplate = $listingItem->getListingTemplate();
            if ($listingItemTemplate !== null) {
                $ratingTypes = $this->container->get('doctrine')->getRepository('AdvancedReviewListingBundle:RatingType')->findBy([
                    'listingTemplateId' => $listingItemTemplate->getId(),
                ]);
            }

            if (empty($ratingTypes) || $params['level']->hasAdvancedReview !== true) {
                $params['_return'] = false;

                return;
            }

            foreach ($ratingTypes as $type) {

                $value = $this->container->get('doctrine')->getRepository('AdvancedReviewListingBundle:ReviewRatingType')->getListingRatingByTypeId($params['item']->getId(), $type->getId());
                $ratings[] = [
                    'label' => $type->getLabel(),
                    'value' => $value ? (int)$value : 0,
                ];
            }

            echo $this->container->get('twig')->render('AdvancedReviewListingBundle::sidebar-stars.html.twig', [
                'ratings' => $ratings,
                'item' => $listingItem,
            ]);

            $params['_return'] = true;

            return;
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getModalWriteReviewOverwriteRateStars(&$params = null)
    {
        if ($params['module'] !== 'listing') {
            $params['_return'] = false;

            return;
        }
        $level = null;
        $ratingTypes = [];
        if (!empty($params['id'])) {
            /**
             * @var Listing $item
             */
            $item = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->find($params['id']);

            $listingItemTemplate = $item->getListingTemplate();

            if ($listingItemTemplate !== null) {
                $ratingTypes = $this->container->get('doctrine')->getRepository('AdvancedReviewListingBundle:RatingType')->findBy([
                    'listingTemplateId' => $listingItemTemplate->getId(),
                ]);
            }

            $listingItemDetail = new ListingItemDetail($this->container, $item);
            $level = $listingItemDetail->getLevel();
        }

        if (empty($ratingTypes) || ($level !== null && $level->hasAdvancedReview !== true)) {
            $params['_return'] = false;

            return;
        }

        echo $this->container->get('twig')->render('AdvancedReviewListingBundle::write-review-stars.html.twig', [
            'ratings' => $ratingTypes,
            'form' => $params['form'],
        ]);

        $params['_return'] = true;
    }

    /**
     * @param null $params
     */
    private function getSearchBeforeRender(&$params = null)
    {
        $languageHandler = $this->container->get('languagehandler');
        $locale = $languageHandler->getISOLang($this->container->get('multi_domain.information')->getLocale());

        $this->container->get('javascripthandler')->addJSBlock('AdvancedReviewListingBundle::js/review-rating-js.html.twig');
        $this->container->get('javascripthandler')->addJSExternalFile('/bundles/advancedreviewlisting/js/fileinput.min.js');
        $this->container->get('javascripthandler')->addJSExternalFile('/bundles/advancedreviewlisting/js/plugins/theme-fa.min.js');

        if ($locale !== 'en') {
            $this->container->get('javascripthandler')->addJSExternalFile('/bundles/advancedreviewlisting/js/locales/' . $locale . '.js');
        }
    }

    /**
     * @param null $params
     */
    private function getReviewTypeAfterBuildForm(&$params = null)
    {
        $params['builder']->add('advancedRating', 'hidden');
        $params['builder']->add('reviewImages', FileType::class, [
            'label' => 'Images',
            'attr' => ['accept' => 'image/*'],
            'required' => false,
            'multiple' => true,
        ]);
    }

    /**
     * @param null $params
     */
    private function getReviewHandlerBeforeReturnSave(&$params = null)
    {
        if ($params['review']->getItemType() !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        if ($data = json_decode($params['data']['advancedRating'], true)) {
            foreach ($data as $rating => $value) {
                if ($rating !== 'undefined') {
                    $statement = $connection->prepare('INSERT INTO Review_RatingType (`review_id`, `rating_id`, `value`) VALUES (:review_id, :rating_id, :rating);');
                    $statement->bindValue('review_id', $params['review']->getId());
                    $statement->bindValue('rating_id', $rating);
                    $statement->bindValue('rating', $value);
                    $statement->execute();
                }
            }
        }

        $accountId = 0;
        if (!empty($params['review']->getProfile())) {
            $accountId = $params['review']->getProfile()->getAccountId();
        }

        if (\is_array($params['data']['reviewImages']) && !empty($params['data']['reviewImages'][0])) {
            $gallery = new Gallery();
            $gallery->setAccountId($accountId);
            $gallery->setTitle($params['review']->getReviewTitle());
            $gallery->setUpdated(new DateTime());
            $gallery->setEntered(new DateTime());

            $manager->persist($gallery);
            $manager->flush($gallery);

            foreach ($params['data']['reviewImages'] as $file) {

                if ($file->isValid() && \in_array($file->getMimeType(),
                        ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'])) {

                    $imageWidth = $this->container->getParameter('advanced_review_listing.review_image_width');
                    $imageHeight = $this->container->getParameter('advanced_review_listing.review_image_height');

                    if ($returnImage = $this->container->get('imageuploader')->saveContentImages($file,
                        $imageWidth, $imageHeight, $this->container->get('multi_domain.information')->getId())) {

                        $image = $manager->getRepository('ImageBundle:Image')->find($returnImage['code']);

                        if (!empty($image)) {
                            $imageGallery = new GalleryImage();
                            $imageGallery->setGalleryId($gallery->getId());
                            $imageGallery->setImage($image);
                            $imageGallery->setImageId($image->getId());
                            $imageGallery->setImageCaption('');
                            $imageGallery->setAltCaption('');
                            $imageGallery->setImageDefault('n');
                            $imageGallery->setOrder(null);

                            $manager->persist($imageGallery);
                            $manager->flush($imageGallery);

                            $reviewGallery = new GalleryItem();
                            $reviewGallery->setGalleryId($gallery->getId());
                            $reviewGallery->setItemId($params['review']->getId());
                            $reviewGallery->setItemType('review');

                            $manager->persist($reviewGallery);
                            $manager->flush($reviewGallery);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param null $params
     */
    private function getListingLevelFeatureBeforeReturn(&$params = null)
    {
        foreach ($params['fields'] as $field) {
            switch ($field->getField()) {
                case DefaultListingLevelFields::ADVANCED_REVIEW :
                    $params['listingLevel']->hasAdvancedReview = true;
                    break;
            }
        }
    }

    /**
     * @param null $params
     */
    private function getListingLevelConstruct(&$params = null)
    {
        $params['that']->hasAdvancedReview = false;
    }

    /**
     * @param null $params
     */
    private function getModalWriteReviewAfterRenderFields(&$params = null)
    {
        if ($params['module'] !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $this->container->get('javascripthandler')->addJSBlock('AdvancedReviewListingBundle::js/write-review-imageupload-js.html.twig');
        $this->container->get('javascripthandler')->addTwigParameter('total_review_images', $this->container->getParameter('advanced_review_listing.total_review_images'));

        echo $this->container->get('twig')->render('AdvancedReviewListingBundle::write-review-imageupload.html.twig',
            [
                'form' => $params['form'],
                'total_review_images' => $this->container->getParameter('advanced_review_listing.total_review_images'),
            ]);
    }

    /**
     * @param null $params
     */
    private function getBaseBeforeRenderStyles(&$params = null)
    {
        echo "<link href='/bundles/advancedreviewlisting/css/fileinput.css' rel='stylesheet'/>";
    }

    /**
     * @param null $params
     */
    private function getReviewDetailAfterReview(&$params = null)
    {
        $gallery = $this->container->get('doctrine')->getEntityManager()->createQueryBuilder()
            ->select('gimg')
            ->from('ImageBundle:GalleryImage', 'gimg')
            ->leftJoin('ImageBundle:GalleryItem', 'gitem', Expr\Join::WITH, 'gitem.galleryId = gimg.galleryId')
            ->where('gitem.itemType = :module')
            ->andWhere('gitem.itemId = :id')
            ->orderBy('gimg.imageDefault', 'DESC')
            ->setMaxResults($this->container->getParameter('advanced_review_listing.total_review_images'))
            ->setParameter('module', 'review')
            ->setParameter('id', $params['review']->getId())
            ->getQuery()
            ->getResult();

        echo $this->container->get('twig')->render('AdvancedReviewListingBundle::review-gallery.html.twig', [
            'gallery' => $gallery,
        ]);
    }

    /**
     * @param null $params
     */
    private function getListingDetailOverwriteReviewJs(&$params = null)
    {
        $languageHandler = $this->container->get('languagehandler');
        $locale = $languageHandler->getISOLang($this->container->get('multi_domain.information')->getLocale());

        $this->container->get('javascripthandler')->addJSBlock('AdvancedReviewListingBundle::js/review-rating-js.html.twig');
        $this->container->get('javascripthandler')->addJSExternalFile('/bundles/advancedreviewlisting/js/fileinput.min.js');
        $this->container->get('javascripthandler')->addJSExternalFile('/bundles/advancedreviewlisting/js/plugins/theme-fa.min.js');

        if ($locale !== 'en') {
            $this->container->get('javascripthandler')->addJSExternalFile('/bundles/advancedreviewlisting/js/locales/' . $locale . '.js');
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsBlocksUtilityDetailOverwriteReviewCount(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $listingLevelFeatures = $params['level'];
                $paginatedReviews = $params['paginated_reviews'];
                if (!empty($item) && !empty($listingLevelFeatures) && $item instanceof Listing) {
                    $data = [
                        'reviewsPaginated' => $paginatedReviews,
                        'item' => $item,
                    ];
                    try {
                        echo $this->container->get('twig')->render('AdvancedReviewListingBundle::detail-reviewstarsmacro-overwrite-reviewcount.html.twig', $data);
                        $returnValue = true;
                    } catch (Twig_Error_Loader $e) {
                        throw $e;
                    } catch (Twig_Error_Runtime $e) {
                        throw $e;
                    } catch (Twig_Error_Syntax $e) {
                        throw $e;
                    } catch (Exception $e) {
                        throw $e;
                    }
                    unset($edirectoryTitleForReviews);
                }
                unset($item, $listingLevelFeatures, $paginatedReviews, $hasReviewButton);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsBlocksUtilityDetailOverwriteReviewCount method of AdvancedReviewListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if ($notLoggedCriticalException !== null) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }
}
