<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaListing;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Repository\ListingRepository;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaListing\Entity\DefaultListingLevelFields;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaListing\Entity\DefaultListingTemplateFields;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaListing\Entity\ListingSocialMedia;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Listing;
use Symfony\Component\Translation\TranslatorInterface;

class AdvancedSocialMediaListingBundle extends Bundle
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
            $translator->trans('hasFacebook', array(), 'advertise');
            $translator->trans('hasTwitter', array(), 'advertise');
            $translator->trans('hasInstagram', array(), 'advertise');
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
                Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                    return $this->getFormPricingAfterAddFields($params);
                });
                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    return $this->getListingCodeAfterSave($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('systemfunct_after_setup_availableformfields', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupAvailableFormFields($params);
                });
                Hooks::Register('systemfunct_after_setup_gamefyitemsfields', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupGamefyItemsFields($params);
                });
                Hooks::Register('systemfunct_after_setup_gamefyitemsactivated', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupGamefyItemsActivated($params);
                });
                Hooks::Register('validatefunct_validate_listing', function (&$params = null) {
                    return $this->getValidateFunctValidateListing($params);
                });

            } else {

                /*
                * Register front only bundle hooks
                */
                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    return $this->getListingCodeAfterSave($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('listinglevel_construct', function (&$params = null) {
                    return $this->getListingLevelConstruct($params);
                });
                Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                    return $this->getListingLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('api_before_returnlisting', function (&$params = null) {
                    return $this->getApiBeforeReturnListing($params);
                });
                Hooks::Register('modulelevelapi_before_return_listingmap', function (&$params = null) {
                    return $this->getModuleLevelApiBeforeReturnListingMap($params);
                });
                Hooks::Register('systemfunct_after_setup_availableformfields', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupAvailableFormFields($params);
                });
                Hooks::Register('systemfunct_after_setup_gamefyitemsfields', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupGamefyItemsFields($params);
                });
                Hooks::Register('systemfunct_after_setup_gamefyitemsactivated', function (&$params = null) {
                    return $this->getSystemFunctAfterSetupGamefyItemsActivated($params);
                });
                Hooks::Register('listingdetail_overwrite_socialnetworking', function (&$params = null) {
                    return $this->getListingDetailOverwriteSocialNetworking($params);
                });
                Hooks::Register('detailextension_overwrite_hassocialnetworking', function (&$params = null) {
                    return $this->getDetailExtensionOverwriteHassocialnetworking($params);
                });
            }
            Hooks::Register('listingform_overwrite_socialnetworking', function (&$params = null) {
                return $this->getListingFormOverwriteSocialNetworking($params);
            });
            Hooks::Register('legacy_formlisting_will_render_socialnetworking_panel', function (&$params = null) {
                return $this->getLegacyFormlistingWillRenderSocialnetworkingPanel($params);
            });
            Hooks::Register('listingtfieldservice-createdefaultlistingtemplatefields_after_setstandardinsertsarray', function (&$params = null) {
                return $this->getListingTFieldServiceCreateDefaultListingTemplateFieldsAfterSetStandardInsertsArray($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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
                            'field' => DefaultListingTemplateFields::FACEBOOK_PAGE
                        ),
                        array(
                            'fieldType' => ListingTField::DEFAULT_TYPE,
                            'field' => DefaultListingTemplateFields::INSTAGRAM_PAGE
                        ),
                        array(
                            'fieldType' => ListingTField::DEFAULT_TYPE,
                            'field' => DefaultListingTemplateFields::TWITTER_PAGE
                        ),
                    );
                    foreach ($standardInserts as $standardInsert) {
                        $standardInsertsArrayRef[] = $standardInsert;
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getListingTFieldServiceCreateDefaultListingTemplateFieldsAfterSetStandardInsertsArray method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getFormPricingAfterAddFields(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if ($params['type'] == 'listing') {
                    $translator = $this->container->get('translator');

                    for ($i = 0, $iMax = count($params['levelOptions']); $i < $iMax; $i++) {
                        if ($params['levelOptions'][$i]['name'] == 'socialNetwork') {
                            unset($params['levelOptions'][$i]);
                        }
                    }

                    $params['levelOptions'][] = [
                        'name' => DefaultListingLevelFields::FACEBOOK_PAGE,
                        'type' => 'checkbox',
                        'title' => $translator->trans('Facebook feed'),
                        'tip' => $translator->trans('Allow owners to add Facebook feed to their listings?'),
                    ];
                    $params['levelOptions'][] = [
                        'name' => DefaultListingLevelFields::TWITTER_PAGE,
                        'type' => 'checkbox',
                        'title' => $translator->trans('Twitter timeline feed'),
                        'tip' => $translator->trans('Allow owners to add Twitter timeline feed to their listings?'),
                    ];
                    $params['levelOptions'][] = [
                        'name' => DefaultListingLevelFields::INSTAGRAM_PAGE,
                        'type' => 'checkbox',
                        'title' => $translator->trans('Instagram feed'),
                        'tip' => $translator->trans('Allow owners to add Instagram feed to their listings?'),
                    ];
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getFormPricingAfterAddFields method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getListingCodeAfterSave(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $em = $this->container->get('doctrine')->getManager();
                $connection = $em->getConnection();

                $twitterPage = (isset($_POST['modstore_twitter']) && !empty($_POST['modstore_twitter'])) ? $_POST['modstore_twitter'] : null;
                $facebookPage = (isset($_POST['modstore_facebook']) && !empty($_POST['modstore_facebook'])) ? $_POST['modstore_facebook'] : null;
                $instagramPage = (isset($_POST['modstore_instagram']) && !empty($_POST['modstore_instagram'])) ? $_POST['modstore_instagram'] : null;

                if ($facebookPage) {
                    $facebookPage = trim($facebookPage);
                    if (string_strpos($facebookPage, '://') === false) {
                        $facebookPage = 'http://' . $facebookPage;
                    }
                    if (preg_match('/\/\s*$/', $facebookPage)) {
                        $facebookPage = substr($facebookPage, 0, -1);
                    }
                    $facebookPage = preg_replace('/\/$/', '', $facebookPage);
                }

                if ($twitterPage) {
                    $twitterPage = trim($twitterPage);
                    if (string_strpos($twitterPage, '://') === false) {
                        $twitterPage = 'http://' . $twitterPage;
                    }
                    if (preg_match('/\/\s*$/', $twitterPage)) {
                        $twitterPage = substr($twitterPage, 0, -1);
                    }
                    $twitterPage = preg_replace('/\/$/', '', $twitterPage);
                }

                $statement = $connection->prepare('SELECT * FROM Listing_SocialMedia WHERE listing_id = :id');
                $statement->bindValue('id', $params['listing']->id);
                $statement->execute();

                $results = $statement->fetch();

                if ($results) {
                    $query = 'UPDATE
				Listing_SocialMedia
			SET
				facebook = :facebook,
				twitter = :twitter,
				instagram = :instagram
			WHERE
				listing_id = :id';
                } else {
                    $query = "INSERT INTO Listing_SocialMedia VALUES (NULL, :id, NULL, :facebook, :twitter, :instagram, 'n')";
                }

                $statement = $connection->prepare($query);
                $statement->bindValue('id', $params['listing']->id);
                $statement->bindValue('facebook', $facebookPage);
                $statement->bindValue('twitter', $twitterPage);
                $statement->bindValue('instagram', $instagramPage);
                $statement->execute();
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getListingCodeAfterSave method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getClassListingBeforeDelete(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $em = $this->container->get('doctrine')->getManager();
                $connection = $em->getConnection();

                $statement = $connection->prepare('DELETE FROM Listing_SocialMedia WHERE listing_id = :id');
                $statement->bindValue('id', $params['that']->id);
                $statement->execute();
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getClassListingBeforeDelete method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getSystemFunctAfterSetupAvailableFormFields(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $params['avFields'][DefaultListingTemplateFields::FACEBOOK_PAGE] = DefaultListingLevelFields::FACEBOOK_PAGE;
                $params['avFields'][DefaultListingTemplateFields::TWITTER_PAGE] = DefaultListingLevelFields::TWITTER_PAGE;
                $params['avFields'][DefaultListingTemplateFields::INSTAGRAM_PAGE] = DefaultListingLevelFields::INSTAGRAM_PAGE;
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getSystemFunctAfterSetupAvailableFormFields method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getSystemFunctAfterSetupGamefyItemsFields(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (in_array(DefaultListingLevelFields::FACEBOOK_PAGE, $params['array_fields'])) {
                    $params['arrayAdditional'][] = DefaultListingLevelFields::FACEBOOK_PAGE;
                }

                if (in_array(DefaultListingLevelFields::TWITTER_PAGE, $params['array_fields'])) {
                    $params['arrayAdditional'][] = DefaultListingLevelFields::TWITTER_PAGE;
                }

                if (in_array(DefaultListingLevelFields::INSTAGRAM_PAGE, $params['array_fields'])) {
                    $params['arrayAdditional'][] = DefaultListingLevelFields::INSTAGRAM_PAGE;
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getSystemFunctAfterSetupGamefyItemsFields method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getSystemFunctAfterSetupGamefyItemsActivated(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $em = $this->container->get('doctrine')->getManager();
                $connection = $em->getConnection();

                $statement = $connection->prepare("SELECT * FROM Listing_SocialMedia WHERE listing_id = :id AND temp = 'n'");
                $statement->bindValue('id', $params['itemObj']->id);
                $statement->execute();

                $results = $statement->fetch();

                if ($results) {

                    if (!empty($results['facebook'])) {
                        $params['additionalFilled'][] = DefaultListingLevelFields::FACEBOOK_PAGE;
                    }

                    if (!empty($results['twitter'])) {
                        $params['additionalFilled'][] = DefaultListingLevelFields::TWITTER_PAGE;
                    }

                    if (!empty($results['instagram'])) {
                        $params['additionalFilled'][] = DefaultListingLevelFields::INSTAGRAM_PAGE;
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getSystemFunctAfterSetupGamefyItemsActivated method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getListingFormOverwriteSocialNetworking(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $em = $this->container->get('doctrine')->getManager();
                $connection = $em->getConnection();

                $twitterPage = null;
                $instagramPage = null;
                $facebookPage = null;

                if ($params['listing']) {

                    $statement = $connection->prepare("SELECT SM.*, Li.title as listing_title FROM Listing_SocialMedia as SM, Listing as Li WHERE SM.listing_id = :id AND Li.id = SM.listing_id AND SM.temp = 'n'");
                    $statement->bindValue('id', $params['listing']->id);
                    $statement->execute();

                    $results = $statement->fetch();

                    if ($results) {
                        $twitterPage = (isset($results['twitter']) && !empty($results['twitter'])) ? $results['twitter'] : null;
                        $facebookPage = (isset($results['facebook']) && !empty($results['facebook'])) ? $results['facebook'] : null;
                        $instagramPage = (isset($results['instagram']) && !empty($results['instagram'])) ? $results['instagram'] : null;
                    }
                }

                echo $this->container->get('templating')->render('AdvancedSocialMediaListingBundle::sitemgr-form-social.html.twig',
                    [
                        'item' => $params['listing'],
                        'twitterPage' => $twitterPage,
                        'instagramPage' => $instagramPage,
                        'facebookPage' => $facebookPage,
                        'array_fields' => $params['array_fields'],
                    ]);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getListingFormOverwriteSocialNetworking method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getListingLevelConstruct(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $params['that']->hasFacebook = false;
                $params['that']->hasTwitter = false;
                $params['that']->hasInstagram = false;
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getListingLevelConstruct method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getListingLevelFeatureBeforeReturn(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                foreach ($params['fields'] as $field) {

                    switch ($field->getField()) {

                        case DefaultListingLevelFields::FACEBOOK_PAGE :
                            $params['listingLevel']->hasFacebook = true;
                            break;

                        case DefaultListingLevelFields::TWITTER_PAGE :
                            $params['listingLevel']->hasTwitter = true;
                            break;

                        case DefaultListingLevelFields::INSTAGRAM_PAGE :
                            $params['listingLevel']->hasInstagram = true;
                            break;
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getListingLevelFeatureBeforeReturn method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getApiBeforeReturnListing(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $social_media = $this->container->get('doctrine')->getRepository('AdvancedSocialMediaListingBundle:ListingSocialMedia')->findBy([
                    'listing' => $params['listing']->getId(),
                ]);

                if ($social_media) {
                    $listingItem = $params['listingItemDetail']->getLevel();

                    if ($social_media[0]->getFacebook() && $listingItem->hasFacebook) {
                        $social_network['facebook'] = $social_media[0]->getFacebook();
                    }
                    if ($social_media[0]->getTwitter() && $listingItem->hasTwitter) {
                        $social_network['twitter'] = $social_media[0]->getTwitter();
                    }

                    $params['listing']->setSocialNetwork($social_network);
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getApiBeforeReturnListing method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getModuleLevelApiBeforeReturnListingMap(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                unset($params['levelItems']['socialNetworking']);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getModuleLevelApiBeforeReturnListingMap method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getLegacyFormlistingWillRenderSocialnetworkingPanel(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $array_fields = $params['array_fields'];
                $renderSocialNetworkingPanel = false;
                if (!empty($array_fields) && is_array($array_fields)) {
                    if(array_search(DefaultListingLevelFields::INSTAGRAM_PAGE, $array_fields)||
                        array_search(DefaultListingLevelFields::TWITTER_PAGE, $array_fields)||
                        array_search(DefaultListingLevelFields::FACEBOOK_PAGE, $array_fields)){
                        $renderSocialNetworkingPanel = true;
                    }
                }
                $params['_return'] = $renderSocialNetworkingPanel;
                unset($renderSocialNetworkingPanel);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyFormlistingWillRenderSocialnetworkingPanel method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getListingDetailOverwriteSocialNetworking(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $em = $this->container->get('doctrine')->getManager();
                $connection = $em->getConnection();

                $statement = $connection->prepare("SELECT SM.*, Li.title as listing_title FROM Listing_SocialMedia as SM, Listing as Li WHERE SM.listing_id = :id AND Li.id = SM.listing_id AND SM.temp = 'n'");
                $statement->bindValue('id', $params['listing']->getId());
                $statement->execute();

                $results = $statement->fetch();

                if (!empty($results) && (!empty($results['twitter']) || !empty($results['facebook']) || !empty($results['instagram']))) {

                    $twitterPage = !empty($results['twitter']) ? $results['twitter'] : null;
                    $facebookPage = !empty($results['facebook']) ? $results['facebook'] : null;
                    $instagramPage = !empty($results['instagram']) ? $results['instagram'] : null;

                    $this->container->get('javascripthandler')->addJSBlock('AdvancedSocialMediaListingBundle::js/instagram.js.twig');

                    echo $this->container->get('templating')->render('AdvancedSocialMediaListingBundle::socialpanel.html.twig',
                        [
                            'twitterPage' => $twitterPage,
                            'instagramPage' => $instagramPage,
                            'facebookPage' => $facebookPage,
                            'level' => $params['level'],
                            'item' => $params['listing'],
                        ]);
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getListingDetailOverwriteSocialNetworking method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getDetailExtensionOverwriteHassocialnetworking(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $em = $this->container->get('doctrine')->getManager();
                $connection = $em->getConnection();

                $statement = $connection->prepare("SELECT facebook,twitter,instagram FROM Listing_SocialMedia WHERE listing_id = :id AND temp = 'n'");
                $statement->bindValue('id', $params['listing']->getId());
                $statement->execute();

                $results = $statement->fetch();

                if (!empty($results)) {
                    $params['listingLevel']->hasFacebook && !empty($results['facebook']) and $params['contentCount']++ and $params['overviewCount']++;
                    $params['listingLevel']->hasTwitter && !empty($results['twitter']) and $params['contentCount']++ and $params['overviewCount']++;
                    $params['listingLevel']->hasInstagram && !empty($results['instagram']) and $params['contentCount']++ and $params['overviewCount']++;
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getDetailExtensionOverwriteHassocialnetworking method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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

    private function getValidateFunctValidateListing(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                if (!empty($params['array']['modstore_instagram']) && preg_match('/^@.+/',
                        $params['array']['modstore_instagram']) === 0) {
                    $translator = $this->container->get('translator');

                    $params['errors'][] = '&#149;&nbsp;' . $translator->trans("Wrong format on instagram value, please use the format '@name'");
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getValidateFunctValidateListing method of AdvancedSocialMediaListingBundle.php', ['exception' => $e]);
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
}
