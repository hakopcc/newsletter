<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services;

use ArcaSolutions\CoreBundle\Services\Settings;
use Facebook\Authentication\OAuth2Client;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphPage;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Acl\Exception\Exception;

class FacebookHelper
{
    /** @var RequestStack $requestStack */
    private $requestStack;
    /** @var Facebook $fb */
    private $fb;
    /** @var FacebookRedirectLoginHelper $redirectLoginHelper */
    private $redirectLoginHelper;
    /** @var Router $router */
    private $router;
    /** @var bool $facebookAppEnabled */
    private $facebookAppEnabled = false;
    /** @var string $schemeAndHttpHost */
    private $schemeAndHttpHost;
    /** @var string $appId */
    private $appId;

    /**
     * FacebookHelper constructor.
     * @param RequestStack $requestStack
     * @param Router $router
     * @param Settings $settings
     */
    public function __construct(RequestStack $requestStack, Router $router, Settings $settings) {
        $currentRequest = $requestStack->getCurrentRequest();
        if($currentRequest !== null) {
            $this->requestStack = $requestStack;
            $this->schemeAndHttpHost = $currentRequest->getSchemeAndHttpHost();
            $this->router = $router;
            $facebookApiId = $settings->getDomainSetting('foreignaccount_facebook_apiid');
            $facebookApiSecret = $settings->getDomainSetting('foreignaccount_facebook_apisecret');
            if (!empty($facebookApiId) && !empty($facebookApiSecret)) {
                try {
                    $this->fb = new Facebook(['app_id' => $facebookApiId, 'app_secret' => $facebookApiSecret, 'default_graph_version' => 'v5.0']);
                    $this->redirectLoginHelper = $this->fb->getRedirectLoginHelper();
                    $this->facebookAppEnabled = !empty($this->redirectLoginHelper);
                    $this->appId = $facebookApiId;
                } catch (FacebookSDKException $e) {
                    //LOG FacebookSDKException here
                } catch (Exception $unexpectedException) {
                    //LOG Unexpected exception here
                }
            }
        }
        unset($facebookApiId, $facebookApiSecret);
    }

    /**
     * @return bool
     */
    public function isFacebookAppEnabled(): bool
    {
        return $this->facebookAppEnabled;
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * @return FacebookRedirectLoginHelper
     */
    public function getRedirectLoginHelper(): FacebookRedirectLoginHelper
    {
        return $this->redirectLoginHelper;
    }

    /**
     * @return OAuth2Client|null
     */
    public function getOAuth2Client()
    {
        if($this->facebookAppEnabled) {
            return $this->fb->getOAuth2Client();
        }
        return null;
    }

    /**
     * @param $token
     * @return array[]|null
     * @throws FacebookSDKException
     */
    public function getUserRelatedFacebookIdArray($token)
    {
        $userRelatedFacebookIdArray = null;
        if($this->facebookAppEnabled) {
            try {
                $userFields = array('id', 'name');
                $userResponse = $this->fb->get('/me?fields=' . implode(',', $userFields), $token);
                $graphUser = $userResponse->getGraphUser();
                $user = array();
                foreach ($userFields as $userField) {
                    $user[$userField] = $graphUser->getField($userField);
                }

                $pages = array();
                if (!empty($user['id'])) {
                    $pageFields = array('id', 'name', 'link');
                    $pagesResponse = $this->fb->get('/' . $user['id'] . '/accounts?fields=' . implode(',', $pageFields), $token);

                    $pagesGraphEdgeResponse = $pagesResponse->getGraphEdge('GraphPage');
                    do {
                        /** @var GraphPage $pageGraph */
                        foreach ($pagesGraphEdgeResponse as $pageGraph) {
                            $page = array();
                            foreach ($pageFields as $pageField) {
                                $page[$pageField] = $pageGraph->getField($pageField);
                            }
                            if(!empty($page)) {
                                $pages[] = $page;
                            }
                        }
                    } while (($pagesGraphEdgeResponse = $this->fb->getPaginationResults($pagesGraphEdgeResponse, 'next')) !== null);
                }
                $userRelatedFacebookIdArray = array('user' => $user, 'pages' => $pages);
            } catch (FacebookSDKException $facebookSdkException) {
                throw $facebookSdkException;
            } catch (Exception $unexpectedException) {
                throw $unexpectedException;
            }
        }
        return $userRelatedFacebookIdArray;
    }

    /**
     * @return string
     */
    public function getLoginUrl(): string
    {
        $loginUrlReturn = '';
        if($this->facebookAppEnabled) {
            $persistentDataHandler = $this->redirectLoginHelper->getPersistentDataHandler();
            if ($persistentDataHandler !== null) {
                $redirectURI_params = [ "origin" => $this->requestStack->getCurrentRequest()->getRequestUri() ];
                $persistentDataHandler->set('state', json_encode($redirectURI_params));
                $permissions = ['pages_show_list']; // Optional permissions
                $faceBookAuthReturnUrl = $this->router->generate(
                    'facebook_helper_login_auth_return', array()
                );
                $loginUrlReturn = $this->redirectLoginHelper->getLoginUrl($this->schemeAndHttpHost . $faceBookAuthReturnUrl, $permissions);
                unset($redirectURI_params, $permissions, $faceBookAuthReturnUrl);
            }
            unset($persistentDataHandler);
        }
        return $loginUrlReturn;
    }
}
