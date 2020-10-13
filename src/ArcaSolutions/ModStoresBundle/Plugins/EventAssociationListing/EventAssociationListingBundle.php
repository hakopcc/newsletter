<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing;

use ArcaSolutions\CoreBundle\Str;
use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Repository\EventRepository;
use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ListingBundle\Repository\ListingRepository;
use ArcaSolutions\ListingBundle\Services\ListingService;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Entity\EventAssociated;
use ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Entity\ListingLevelFieldEvents;
use ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Services\EventAssociationService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Elastica\Result;
use Exception;
use PDO;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class EventAssociationListingBundle extends Bundle
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
            $translator->trans('eventsCount', array(), 'advertise');
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
                    $this->getLegacySitemgrContentListingTemplateBeforeRenderWidgetPlaceholder($this::MAIN_WIDGET_SECTION_NAME, $params);
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
                Hooks::Register('eventcode_after_save', function (&$params = null) {
                    $this->getEventCodeAfterSave($params);
                });
                Hooks::Register('classevent_before_delete', function (&$params = null) {
                    $this->getClassEventBeforeDelete($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    $this->getSitemgrHeaderAfterRenderMetatags($params);
                });
                Hooks::Register('formevent_after_render_renewaldate', function (&$params = null) {
                    $this->getFormEventAfterRenderRenewalDate($params);
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
                Hooks::Register('eventcode_after_save', function (&$params = null) {
                    $this->getEventCodeAfterSave($params);
                });
                Hooks::Register('classevent_before_delete', function (&$params = null) {
                    $this->getClassEventBeforeDelete($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('eventdetail_after_render_contact', function (&$params = null) {
                    $this->getEventDetailAfterRenderContact($params);
                });
                Hooks::Register('formevent_after_render_renewaldate', function (&$params = null) {
                    $this->getFormEventAfterRenderRenewalDate($params);
                });
                Hooks::Register('detailextension_after_setlistingwidgettwigname', function (&$params = null) {
                    $this->getDetailExtensionAfterSetListingWidgetTwigName($params);
                });
                Hooks::Register('detailextension_before_settabhascontent', function (&$params = null) {
                    $this->getDetailExtensionBeforeSetTabHasContent($params);
                });
                Hooks::Register('blocksextension_overwrite_recurringdata', function (&$params = null) {
                    $this->getBlockExtensionOverwriteRecurringData($params);
                });
                Hooks::Register('event_after_validate_itemdetail', function (&$params = null) {
                    $this->getEventAfterValidateItemDetail($params);
                });
            }
            Hooks::Register('classevent_on_return_hasrenewaldate', function (&$params = null) {
                $this->getClassEventOnReturnHasRenewalDate($params);
            });
            Hooks::Register('listingcode_after_save', function (&$params = null) {
                $this->getListingCodeAfterSave($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                    /** @var EventAssociationService $eventAssociationSvc */
                    $eventAssociationSvc = $this->container->get('plugin.eventassociation.service');
                    if ($eventAssociationSvc !== null) {
                        if (!empty($listing) && !empty($listing->id)) {
                            $eventAssociationSvc->updateEventAssociations($listing->id);
                        }
                    }
                    unset($eventAssociationSvc);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingCodeAfterSave method of EventAssociationBundle.php', ['exception' => $e]);
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
                        'name' => 'events',
                        'type' => 'numeric',
                        'title' => $translation->trans('Event Association'),
                        'tip' => $translation->trans('Number of Events the listing owner is able to associate'),
                        'min' => 0,
                    ];
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getFormPricingAfterAddFields method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                if ($params['type'] == 'listing' && $params['levelOptionData']['events']) {

                    $doctrine = $this->container->get('doctrine');
                    $manager = $this->container->get('doctrine')->getManager();

                    foreach ($params['levelOptionData']['events'] as $level => $field) {

                        $listingLevel = $doctrine->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
                            'level' => $level,
                        ]);

                        if ($listingLevel) {
                            $listingLevel->setField($field);
                            $manager->persist($listingLevel);
                        } else {
                            $listingLevel = new ListingLevelFieldEvents();
                            $listingLevel->setLevel($level);
                            $listingLevel->setField($field);
                            $manager->persist($listingLevel);
                        }
                    }

                    $manager->flush();
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getPaymentGatewayAfterSaveLevels method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                if (is_a($params['levelObj'], 'ListingLevel') && $params['option']['name'] == 'events') {

                    $params['levelObj']->events = [];

                    $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findBy([],
                        ['level' => 'DESC']);

                    if ($resultLevel) {
                        foreach ($resultLevel as $levelfield) {
                            $params['levelObj']->events[] = $levelfield->getField();
                        }
                    }
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getFormLevelsRenderFields method of EventAssociationListingBundle.php', ['exception' => $e]);
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
    private function getSitemgrListingTabsAfterRenderTabs(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $translation = $this->container->get('translator');

                $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
                    'level' => $params['listing']->getNumber('level'),
                ]);

                if (!empty($resultLevel) && EVENT_FEATURE == 'on' && CUSTOM_EVENT_FEATURE == 'on' && $resultLevel->getField() > 0) {
                    printf('<li %s><a href="%s/event.php?id=%d" role="tab">%s</a></li>',
                        $params['activeTab']['event'],
                        $params['url_redirect'],
                        $params['id'],
                        ucfirst($translation->trans('Event'))
                    );
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getSitemgrListingTabsAfterRenderTabs method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                if (string_strpos($_SERVER['PHP_SELF'], 'content/' . EVENT_FEATURE_FOLDER . '/') !== false ||
                    string_strpos($_SERVER['PHP_SELF'], 'sponsors/' . EVENT_FEATURE_FOLDER . '/') !== false) {

                    $request = $this->container->get('request_stack')->getCurrentRequest();
                    if ($request !== null) {
                        $attached_listing = $request->get('listing_id', 0);
                    }

                    if (empty($attached_listing) && !empty($params['id'])) {
                        $manager = $this->container->get('doctrine')->getManager();
                        $connection = $manager->getConnection();

                        $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
                        $statement->bindValue('event_id', $params['id']);
                        $statement->execute();

                        $attached_listing = $statement->fetch()['listing_id'];
                    }

                    echo $this->container->get('twig')->render('EventAssociationListingBundle::js/event_form_association.html.twig',
                        [
                            'members' => $params['members'],
                            'attached_listing' => $attached_listing,
                            'id' => $params['id']
                        ]);

                }

                if (string_strpos($_SERVER['PHP_SELF'], 'content/' . LISTING_FEATURE_FOLDER . '/event') !== false) {

                    $listing = null;
                    $attached_event = [];

                    if (isset($params['id'])) {

                        /** @var DoctrineRegistry $doctrine */
                        $doctrine = $this->container->get('doctrine');
                        /** @var Listing $listing */
                        $listing = $doctrine->getRepository('ListingBundle:Listing')->find($params['id']);

                        /** @var EventAssociated[] $selectedListingEvents */
                        $selectedListingEvents = $doctrine->getRepository('EventAssociationListingBundle:EventAssociated')->findBy(['listingId' => $params['id']]);
                        $attached_event = array_reduce($selectedListingEvents, function ($carry, $item) {
                            if ($item instanceof EventAssociated) {
                                /** @var EventAssociated $item */
                                if (empty($carry)) {
                                    $carry = array();
                                }
                                /** @var Event $event */
                                $event = $item->getEvent();
                                if ($event !== null && !in_array($event->getId(), $carry, true)) {
                                    $carry[] = $event->getId();
                                }
                            }
                            return $carry;
                        });

                        if ($attached_event === null) {
                            $attached_event = array();
                        }

                        $associationLevel = $doctrine->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy(['level' => $listing->getLevel()]);
                    }

                    echo $this->container->get('twig')->render('EventAssociationListingBundle::js/listing_form_association.html.twig',
                        [
                            'members' => $params['members'],
                            'listing' => $listing,
                            'level' => $associationLevel,
                            'attached_event' => str_replace('"', '\"', json_encode($attached_event)),
                        ]);

                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getModulesFooterAfterRenderJs method of EventAssociationListingBundle.php', ['exception' => $e]);
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
    private function getEventCodeAfterSave(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $listing_attached = (isset($_POST['listing_id']) && !empty($_POST['listing_id'])) ? $_POST['listing_id'] : null;

                $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :id');
                $statement->bindValue('id', $params['event']->getNumber('id'));
                $statement->execute();

                $results = $statement->fetch();

                $statement = null;
                if ($results) {
                    if ($listing_attached !== null) {
                        $statement = $connection->prepare('UPDATE EventAssociated SET listing_id = :listingId WHERE event_id = :id');
                    } else {
                        $statement = $connection->prepare('DELETE FROM EventAssociated WHERE event_id = :id AND listing_id = :listingId');
                    }
                } else {
                    if ($listing_attached !== null) {
                        $statement = $connection->prepare('INSERT INTO EventAssociated (event_id, listing_id) VALUES (:id, :listingId)');
                    }
                }

                if ($statement !== null) {
                    $statement->bindValue('id', $params['event']->getNumber('id'));
                    $statement->bindValue('listingId', ($listing_attached !== null) ? $listing_attached : $results['listing_id']);
                    $statement->execute();
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getEventCodeAfterSave method of EventAssociationListingBundle.php', ['exception' => $e]);
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
    private function getClassEventBeforeDelete(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $statement = $connection->prepare('DELETE FROM EventAssociated WHERE event_id = :id');
                $statement->bindValue('id', $params['that']->id);
                $statement->execute();
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getClassEventBeforeDelete method of EventAssociationListingBundle.php', ['exception' => $e]);
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

                $statement = $connection->prepare('DELETE FROM EventAssociated WHERE listing_id = :id');
                $statement->bindValue('id', $params['that']->id);
                $statement->execute();
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getClassListingBeforeDelete method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                    $logger->critical('Unexpected error on getSitemgrHeaderAfterRenderMetatags method of EventAssociationListingBundle.php', ['exception' => $e]);
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
    private function getFormEventAfterRenderRenewalDate(&$params = null): void
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
	                $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
	                $statement->bindValue('event_id', $params['id']);



	                $statement->execute();
					$listing_id = $statement->fetch()['listing_id'];
					if (empty($listing_id)) {
                        $listing_id = null;
                    }
				}

                echo $this->container->get('templating')->render('EventAssociationListingBundle::form-sitemgr-event.html.twig', [
                    'listing_id' => $listing_id
                ]);
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getFormEventAfterRenderRenewalDate method of EventAssociationListingBundle.php', ['exception' => $e]);
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

                $statement = $connection->prepare('SELECT id FROM EventAssociated WHERE listing_id = :listing_id LIMIT 1');
                $statement->bindValue('listing_id', $params['listing']->getId());
                $statement->execute();

                $associationId = $statement->fetch(PDO::FETCH_COLUMN);

                $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
                    'level' => $params['listing']->getLevel(),
                ]);

                !empty($resultLevel) and $num_events_allowed = $resultLevel->getField();

                if (!empty($associationId) && !empty($num_events_allowed)) {
                    $params['contentCount']++;
                    $params['activeTab'] = $params['activeTab'] < 6 ? 6 : $params['activeTab'];
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getDetailExtensionOverwriteActiveTab method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                $params['that']->eventsCount = 0;
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingLevelConstruct method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
                    'level' => $params['level']->getValue(),
                ]);

                if (!empty($resultLevel)) {
                    $params['listingLevel']->eventsCount = $resultLevel->getField();
                } else {
                    $params['listingLevel']->eventsCount = 0;
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingLevelFeatureBeforeReturn method of EventAssociationListingBundle.php', ['exception' => $e]);
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

                $events = null;

                $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
                    'level' => $params['item']->getLevel(),
                ]);

                !empty($resultLevel) and $num_events_allowed = $resultLevel->getField();

                if (!empty($num_events_allowed)) {
                    $dateNow = new DateTime('now');
                    $statement = $connection->prepare("SELECT id FROM Event WHERE status = :status AND id IN (SELECT event_id FROM EventAssociated WHERE listing_id = :listing_id) AND (((until_date >= :now OR DATE_FORMAT(until_date, '%Y-%m-%d') = :emptyDate) AND recurring = :yes) OR (end_date >= :now AND recurring = :no)) ORDER BY id LIMIT :limit");
                    $statement->bindValue('status', 'A');
                    $statement->bindValue('listing_id', $params['item']->getId());
                    $statement->bindValue('now', $dateNow->format('Y-m-d'));
                    $statement->bindValue('emptyDate', '0000-00-00');
                    $statement->bindValue('yes', 'Y');
                    $statement->bindValue('no', 'N');
                    $statement->bindValue('limit', (int)$num_events_allowed, PDO::PARAM_INT);
                    $statement->execute();

                    $resEvents = $statement->fetchAll();

                    foreach ($resEvents as $event) {
                        /** @var Event $tmpEvent */
                        $tmpEvent = $this->container->get('doctrine')->getRepository('EventBundle:Event')->find($event['id']);
//                        $tmpEvent->setRecurring(['enabled' => $tmpEvent->getRecurring() === 'Y']);
//                        $tmpEvent->date = [
//                            'start' => $tmpEvent->getStartDate(),
//                            'end' => $tmpEvent->getEndDate(),
//                        ];
//                        $tmpEvent->event = $tmpEvent;
                        $events[] = $tmpEvent;
                    }

                    $this->container->get('twig')->addGlobal('eventsAssoc', $events);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getListingBeforeAddGlobalVars method of EventAssociationListingBundle.php', ['exception' => $e]);
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
    private function getEventDetailAfterRenderContact(&$params = null): void
    {
        if (!empty($params) && !empty($this->container)) {
            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $manager = $this->container->get('doctrine')->getManager();
                $connection = $manager->getConnection();

                $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
                $statement->bindValue('event_id', $params['item']->getId());
                $statement->execute();

                $listingId = $statement->fetch(PDO::FETCH_COLUMN);

                if (!empty($listingId)) {

                    $listing = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->findOneBy([
                        'id' => $listingId,
                        'status' => 'A',
                    ]);

                    if ($listing) {

                        $resultLevel = $this->container->get('doctrine')->getRepository('EventAssociationListingBundle:ListingLevelFieldEvents')->findOneBy([
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

                            echo $this->container->get('templating')->render('EventAssociationListingBundle::eventassoc-eventdetail.html.twig',
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
                    $logger->critical('Unexpected error on getEventDetailAfterRenderOverview method of EventAssociationListingBundle.php', ['exception' => $e]);
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
        $widgetSectionValidNames = array($this::HEADER_WIDGET_SECTION_NAME, $this::MAIN_WIDGET_SECTION_NAME, $this::SIDEBAR_WIDGET_SECTION_NAME);
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
                        if ($listingWidget !== null && $listingWidget->getTitle() === ListingWidgetAssociatedEvent::LISTING_WIDGET_TITLE) {
                            if ($this->container->hasParameter('alias_sitemgr_module')) {
                                $siteManagerAlias = $this->container->getParameter('alias_sitemgr_module');
                                /** @var KernelInterface $kernel */
                                $kernel = $this->container->get('kernel');
                                if ($kernel !== null && !empty($siteManagerAlias)) {
                                    $kernelRootDir = $kernel->getRootDir();
                                    if (!empty($kernelRootDir)) {
                                        /** @var RequestStack $requestStackFromContainer */
                                        $requestStack = $this->container->get('request_stack');
                                        if ($requestStack !== null) {
                                            /** @var Request $currentRequest */
                                            $currentRequest = $requestStack->getCurrentRequest();
                                            if ($currentRequest !== null) {
                                                $schemeAndHttpHost = $currentRequest->getSchemeAndHttpHost();
                                                if (!empty($schemeAndHttpHost)) {
                                                    $placeHolderFileName = 'associated-events.jpg';
                                                    $listingWidgetImagesPathFromSiteMgr = 'assets/img/listing-widget-placeholder/' . $widgetSectionName;
                                                    $physicalListingWidgetImagesPath = $kernelRootDir . '/../web/' . $siteManagerAlias . '/' . $listingWidgetImagesPathFromSiteMgr;
                                                    $httpListingWidgetImagesPath = $schemeAndHttpHost . '/' . $siteManagerAlias . '/' . $listingWidgetImagesPathFromSiteMgr;
                                                    if (file_exists($physicalListingWidgetImagesPath . '/' . $placeHolderFileName)) {
                                                        $imagePathRef = $httpListingWidgetImagesPath . '/' . $placeHolderFileName;
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
                    $logger->critical('Unexpected error on getLegacySitemgrContentListingTemplateBeforeRenderWidgetPlaceholder method of EventAssociationListingBundle.php', ['exception' => $e]);
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
                $listingTemplateListingWidget = $params['listingtemplate_listingwidget'];
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
                            if($twigLoader->exists('EventAssociationListingBundle' . $listingWidgetTwigNameRef)) {//if exists and is independent of the section
                                $listingWidgetTwigNameRef = 'EventAssociationListingBundle' . $listingWidgetTwigNameRef;
                            } elseif ($twigLoader->exists('EventAssociationListingBundle' . $listingWidgetSectionTwigName)) {//if exists and is dependent of the section
                                $listingWidgetTwigNameRef = 'EventAssociationListingBundle' . $listingWidgetSectionTwigName;
                            }
                        }
                        unset($twigLoader);
                    }
                    unset($twig);
                }
            } catch (Exception $e) {
                if ($logger !== null) {
                    $logger->critical('Unexpected error on getDetailExtensionAfterSetListingWidgetFilePath method of EventAssociationListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger, $listingTemplateListingWidget, $listingWidgetSection, $twig, $twigLoader);
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
                if ($hasContentRef !== null && !empty($listing) && !empty($listingLevel) && $tab !== null && !empty($tabSectionWidgets)) {
                    $tabHasEventAssociationWidget = false;
                    foreach ($tabSectionWidgets as $sectionWidgets) {
                        /** @var ListingTemplateListingWidget $listingTemplateListingWidget */
                        foreach ($sectionWidgets as $listingTemplateListingWidget) {
                            $listingWidget = $listingTemplateListingWidget->getListingWidget();
                            if ($listingWidget !== null && $listingWidget->getTitle() === ListingWidgetAssociatedEvent::LISTING_WIDGET_TITLE) {
                                $tabHasEventAssociationWidget = true;
                            }
                            unset($listingWidget);
                        }
                    }
                    if ($tabHasEventAssociationWidget) {
                        $willRender = false;
                        if (property_exists($listingLevel, 'eventsCount') && !empty($listingLevel->eventsCount)) {
                            /**
                             * @var DoctrineRegistry $doctrine
                             */
                            $doctrine = $this->container->get("doctrine");
                            if ($doctrine !== null) {
                                /** @var ObjectManager $manager */
                                $manager = $doctrine->getManager();
                                /** @var Connection $connection */
                                $connection = $manager->getConnection();

                                $dateNow = new DateTime('now');
                                $statement = $connection->prepare("SELECT id FROM Event WHERE status = :status AND id IN (SELECT event_id FROM EventAssociated WHERE listing_id = :listing_id) AND (((until_date >= :now OR DATE_FORMAT(until_date, '%Y-%m-%d') = :emptyDate) AND recurring = :yes) OR (end_date >= :now AND recurring = :no)) ORDER BY id LIMIT :limit");
                                $statement->bindValue('status', 'A');
                                $statement->bindValue('listing_id', $listing->getId());
                                $statement->bindValue('now', $dateNow->format('Y-m-d'));
                                $statement->bindValue('emptyDate', '0000-00-00');
                                $statement->bindValue('yes', 'Y');
                                $statement->bindValue('no', 'N');
                                $statement->bindValue('limit', (int)$listingLevel->eventsCount, PDO::PARAM_INT);
                                $statement->execute();

                                $resEventsRowCount = $statement->rowCount();
                                $willRender = !empty($resEventsRowCount);
                                unset($resEventsRowCount, $statement, $connection, $manager);
                            }
                            unset($doctrine);
                        }
                        $hasContentRef = ($hasContentRef || $willRender);
                        unset($willRender);
                    }
                    unset($tabHasEventAssociationWidget);
                }
                unset($tab, $listing, $listingLevel, $tabSectionWidgets);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getDetailExtensionBeforeSetTabHasContent method of EventAssociationListingBundle.php', ['exception' => $e]);
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
    private function getClassEventOnReturnHasRenewalDate($params): void
    {
        if (!empty($params) && !empty($this->container)) {
            $returnValue = false;
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $classEventObjRef = &$params['that'];
                if (property_exists($classEventObjRef, 'id') && !empty($classEventObjRef->id)) {
                    /** @var DoctrineRegistry $doctrine */
                    $doctrine = $this->container->get("doctrine");
                    /** @var ListingService $listingService */
                    $listingService = $this->container->get("listing.service");
                    if ($doctrine !== null && $listingService !== null) {
                        /** @var ListingRepository $listingRepository */
                        $listingRepository = $doctrine->getRepository('ListingBundle:Listing');
                        /** @var EventRepository $articleRepository */
                        $eventRepository = $doctrine->getRepository('EventBundle:Event');
                        /** @var EntityRepository $eventAssociatedRepository */
                        $eventAssociatedRepository = $doctrine->getRepository('EventAssociationListingBundle:EventAssociated');
                        if ($eventAssociatedRepository !== null && $eventRepository!==null && $listingRepository !== null) {
                            /** @var Event $event */
                            $event = $eventRepository->find($classEventObjRef->id);
                            if ($event !== null) {
                                /** @var EventAssociated $eventAssociated */
                                $eventAssociated = $eventAssociatedRepository->findOneBy(['event' => $event]);
                                if ($eventAssociated !== null) {
                                    $listingId = $eventAssociated->getListingId();
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
                                }
                                unset($articleAssociated);
                            }
                            unset($event);
                        }
                        unset($eventAssociatedRepository, $eventRepository, $listingRepository);
                    }
                    unset($listingService);
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getClassEventOnReturnHasRenewalDate method of EventAssociationListingBundle.php', ['exception' => $e]);
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

    /**
     * Obs.: Necessary to ModStoresBundle\Plugins\EventAssociationListing\Resources\views\eventassoc-eventdetail.html.twig due to usage of  UtilityDetail.offerBy(listing,listingLevel,listingReviewsTotal,listingDetailURL)
     *
     * @param null $params
     * @throws Exception
     */
    private function getEventAfterValidateItemDetail(&$params = null): void
    {
        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $statement = $connection->prepare('SELECT listing_id FROM EventAssociated WHERE event_id = :event_id LIMIT 1');
        $statement->bindValue('event_id', $params['item']->getId());
        $statement->execute();

        $attached_listing = $statement->fetch()['listing_id'];

        if (!empty($attached_listing)) {
            $listing = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->find($attached_listing);
            $listingItemDetail = new ListingItemDetail($this->container, $listing);

            $reviewTotal = $this->container->get('doctrine')->getRepository('WebBundle:Review')->getReviewsPaginated($attached_listing, 1);

            !empty($reviewTotal) and $this->container->get('twig')->addGlobal('listingReviewsTotal', $reviewTotal['total']);
            $listingItemDetail !== null and $this->container->get('twig')->addGlobal('listingLevel', $listingItemDetail->getLevel());
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getBlockExtensionOverwriteRecurringData(&$params = null): void
    {
        if ($params['item'] instanceof Result) {
            $params['_return'] = false;
        } elseif($params['item'] instanceof Event) {
            /** @var Event $item */
            $item = $params['item'];
            $recurringFromItem = $item->getRecurring();
            $startDateFromItem = $item->getStartDate();
            if (!empty($recurringFromItem) && !empty($startDateFromItem) && Str::upper($recurringFromItem)==='Y') {
                $untilDateFromItem = $item->getUntilDate();
                $dateStart = $this->container->get('event.recurring.service')->getNextOccurrence(
                    $startDateFromItem,
                    $untilDateFromItem,
                    str_replace('RRULE:', '', $this->container->get('event.recurring.service')->getRRule_rfc2445($item))
                );
            } else {
                $dateStart = $startDateFromItem;
            }

            if (!empty($dateStart)) {
                $data['weekDay'] = $dateStart->format('w') + 1;
                $data['month'] = $dateStart->format('m');
                $data['day'] = $dateStart->format('d');
            }
        }
    }

}

