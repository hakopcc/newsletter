<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaEvent;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaEvent\Entity\EventSocialMedia;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

class AdvancedSocialMediaEventBundle extends Bundle
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
                Hooks::Register('eventcode_after_save', function (&$params = null) {
                    return $this->getEventCodeAfterSave($params);
                });
                Hooks::Register('classevent_before_delete', function (&$params = null) {
                    return $this->getClassEventBeforeDelete($params);
                });
                Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                    return $this->getFormPricingAfterAddFields($params);
                });
                Hooks::Register('eventform_overwrite_facebookpage', function (&$params = null) {
                    return $this->getEventFormOverwriteFacebookPage($params);
                });
                Hooks::Register('validatefunct_validate_event', function (&$params = null) {
                    return $this->getValidateFunctValidateEvent($params);
                });

            } else {

                /*
                * Register front only bundle hooks
                */
                Hooks::Register('eventcode_after_save', function (&$params = null) {
                    return $this->getEventCodeAfterSave($params);
                });
                Hooks::Register('classevent_before_delete', function (&$params = null) {
                    return $this->getClassEventBeforeDelete($params);
                });
                Hooks::Register('eventlevel_construct', function (&$params = null) {
                    return $this->getEventLevelConstruct($params);
                });
                Hooks::Register('eventlevelfeature_before_return', function (&$params = null) {
                    return $this->getEventLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('eventdetail_overwrite_facebookpage', function (&$params = null) {
                    return $this->getEventDetailOverwriteFacebookPage($params);
                });
                Hooks::Register('event_detailextension_eventcontent_overwrite_hasfacebookpage', function (&$params = null) {
                    return $this->getEventDetailextensionEventcontentOverwriteHasfacebookpage($params);
                });
                Hooks::Register('eventform_overwrite_facebookpage', function (&$params = null) {
                    return $this->getEventFormOverwriteFacebookPage($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of AdvancedSocialMediaEventBundle.php', ['exception' => $e]);
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

    private function getEventCodeAfterSave(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();
        $event = $params['event'];

        $twitterPage = (isset($_POST['modstore_twitter']) && !empty($_POST['modstore_twitter'])) ? $_POST['modstore_twitter'] : null;
        $facebookPage = (isset($_POST['modstore_facebook']) && !empty($_POST['modstore_facebook'])) ? $_POST['modstore_facebook'] : null;
        $instagramPage = (isset($_POST['modstore_instagram']) && !empty($_POST['modstore_instagram'])) ? $_POST['modstore_instagram'] : null;

        if ($facebookPage) {
            $facebookPage = trim($facebookPage);
            if (string_strpos($facebookPage, '://') === false) {
                $facebookPage = 'http://'.$facebookPage;
            }
            if (preg_match('/\/\s*$/', $facebookPage)) {
                $facebookPage = substr($facebookPage, 0, -1);
            }
            $facebookPage = preg_replace('/\/$/', '', $facebookPage);
        }

        if ($twitterPage) {
            $twitterPage = trim($twitterPage);
            if (string_strpos($twitterPage, '://') === false) {
                $twitterPage = 'http://'.$twitterPage;
            }
            if (preg_match('/\/\s*$/', $twitterPage)) {
                $twitterPage = substr($twitterPage, 0, -1);
            }
            $twitterPage = preg_replace('/\/$/', '', $twitterPage);
        }

        $statement = $connection->prepare('SELECT * FROM Event_SocialMedia WHERE event_id = :id');
        $statement->bindValue('id', $event->id);
        $statement->execute();

        $results = $statement->fetch();

        if ($results) {
            $query = 'UPDATE
				Event_SocialMedia
			SET
				facebook = :facebook,
				twitter = :twitter,
				instagram = :instagram
			WHERE
				event_id = :id';
        } else {
            $query = "INSERT INTO Event_SocialMedia VALUES (NULL, :id, NULL, :facebook, :twitter, :instagram, 'n')";
        }

        $statement = $connection->prepare($query);
        $statement->bindValue('id', $event->id);
        $statement->bindValue('facebook', $facebookPage);
        $statement->bindValue('twitter', $twitterPage);
        $statement->bindValue('instagram', $instagramPage);
        $statement->execute();
    }


    private function getClassEventBeforeDelete(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Event_SocialMedia WHERE event_id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();
    }

    private function getFormPricingAfterAddFields(&$params = null)
    {
        if ($params['type'] == 'event') {

            $translator = $this->container->get('translator');

            for ($i = 0, $iMax = count($params["levelOptions"]); $i < $iMax; $i++) {
                if ($params['levelOptions'][$i]['name'] == 'fbpage') {
                    unset($params['levelOptions'][$i]);
                }
            }

            $params['levelOptions'][] = [
                'name'  => 'facebook_page',
                'type'  => 'checkbox',
                'title' => $translator->trans('Facebook feed'),
                'tip'   => $translator->trans('Allow owners to add Facebook feed to their events?'),
            ];
            $params['levelOptions'][] = [
                'name'  => 'twitter_page',
                'type'  => 'checkbox',
                'title' => $translator->trans('Twitter timeline feed'),
                'tip'   => $translator->trans('Allow owners to add Twitter timeline feed to their events?'),
            ];
            $params['levelOptions'][] = [
                'name'  => 'instagram_page',
                'type'  => 'checkbox',
                'title' => $translator->trans('Instagram feed'),
                'tip'   => $translator->trans('Allow owners to add Instagram feed to their events?'),
            ];
        }
    }

    private function getEventFormOverwriteFacebookPage(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $twitterPage = null;
        $instagramPage = null;
        $facebookPage = null;

        if ($params['event']) {

            $statement = $connection->prepare("SELECT SM.*, Li.title as event_title FROM Event_SocialMedia as SM, Event as Li WHERE SM.event_id = :id AND Li.id = SM.event_id AND SM.temp = 'n'");
            $statement->bindValue('id', $params['event']->id);
            $statement->execute();

            $results = $statement->fetch();

            if ($results) {
                $twitterPage = (isset($results['twitter']) && !empty($results['twitter'])) ? $results['twitter'] : null;
                $facebookPage = (isset($results['facebook']) && !empty($results['facebook'])) ? $results['facebook'] : null;
                $instagramPage = (isset($results['instagram']) && !empty($results['instagram'])) ? $results['instagram'] : null;
            }
        }

        echo $this->container->get('templating')->render('AdvancedSocialMediaEventBundle::sitemgr-form-social.html.twig',
            [
                'item'          => $params['event'],
                'twitterPage'   => $twitterPage,
                'instagramPage' => $instagramPage,
                'facebookPage'  => $facebookPage,
                'array_fields'  => $params['array_fields'],
            ]);
    }

    private function getEventLevelConstruct(&$params = null)
    {
        $params['that']->hasFacebook = false;
        $params['that']->hasTwitter = false;
        $params['that']->hasInstagram = false;
    }

    private function getEventLevelFeatureBeforeReturn(&$params = null)
    {
        foreach ($params['fields'] as $field) {

            switch ($field->getField()) {

                case 'facebook_page' :
                    $params['eventLevel']->hasFacebook = true;
                    break;

                case 'twitter_page' :
                    $params['eventLevel']->hasTwitter = true;
                    break;

                case 'instagram_page' :
                    $params['eventLevel']->hasInstagram = true;
                    break;
            }
        }
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getEventDetailextensionEventcontentOverwriteHasfacebookpage(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                /**
                 * @var Event $event
                 */
                $event = $params['event'];
                $contentCountRef = &$params['contentCount'];
                if (!empty($event)&&isset($contentCountRef)&&is_numeric($contentCountRef)) {
                    $container = $this->container;
                    if(!empty($container)) {
                        $doctrine = $container->get('doctrine');
                        if (!empty($doctrine)) {
                            /**
                             * @var ObjectRepository $eventSocialMediaRepository
                             */
                            $eventSocialMediaRepository = $doctrine->getRepository('AdvancedSocialMediaEventBundle:EventSocialMedia');
                            if (!empty($eventSocialMediaRepository)) {
                                /**
                                 * @var EventSocialMedia $eventSocialMedia
                                 */
                                $eventSocialMedia=$eventSocialMediaRepository->findOneBy(['event'=>$event]);
                                if(!empty($eventSocialMedia)){
                                    $facebook = $eventSocialMedia->getFacebook();
                                    $instagram = $eventSocialMedia->getInstagram();
                                    $twitter = $eventSocialMedia->getTwitter();
                                    if(!empty($facebook)){
                                        $contentCountRef++;
                                    }
                                    if(!empty($instagram)){
                                        $contentCountRef++;
                                    }
                                    if(!empty($twitter)){
                                        $contentCountRef++;
                                    }
                                    unset($facebook, $instagram, $twitter);
                                }
                            }
                        }
                    }
                    unset($container);
                }
                unset($event);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getEventDetailextensionEventcontentOverwriteHasfacebookpage method of AdvancedSocialMediaEventBundle.php', ['exception' => $e]);
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

    private function getEventDetailOverwriteFacebookPage(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare("SELECT SM.*, Li.title as event_title FROM Event_SocialMedia as SM, Event as Li WHERE SM.event_id = :id AND Li.id = SM.event_id AND SM.temp = 'n'");
        $statement->bindValue('id', $params['event']->getId());
        $statement->execute();

        $results = $statement->fetch();

        if ($results) {

            $twitterPage = (isset($results['twitter']) && !empty($results['twitter'])) ? $results['twitter'] : null;
            $facebookPage = (isset($results['facebook']) && !empty($results['facebook'])) ? $results['facebook'] : null;
            $instagramPage = (isset($results['instagram']) && !empty($results['instagram'])) ? $results['instagram'] : null;

            $this->container->get('javascripthandler')->addJSBlock('AdvancedSocialMediaEventBundle::js/instagram.js.twig');

            echo $this->container->get('templating')->render('AdvancedSocialMediaEventBundle::socialpanel.html.twig', [
                'twitterPage'   => $twitterPage,
                'instagramPage' => $instagramPage,
                'facebookPage'  => $facebookPage,
                'level'         => $params['level'],
                'item'          => $params['event'],
            ]);
        }
    }

    private function getValidateFunctValidateEvent(&$params = null)
    {
        if (!empty($params['array']['modstore_instagram']) && preg_match('/^@.+/',
                $params['array']['modstore_instagram']) === 0) {
            $translator = $this->container->get('translator');

            $params['errors'][] = '&#149;&nbsp;'.$translator->trans("Wrong format on instagram value, please use the format '@name'");
        }
    }
}
