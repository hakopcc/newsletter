<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Controller;

use Exception;
use Facebook\Exceptions\FacebookSDKException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function returnFbAuthAction(Request $request)
    {
        $facebookIdArray = array();
        $redirectUrl = $request->getSchemeAndHttpHost();
        $requestQueryState = null;
        if ($request->query->has('state')) {
            $requestQueryState = $request->query->get('state');
            $decodedState = json_decode($requestQueryState,false);
            if(property_exists($decodedState,'origin')) {
                $redirectUrl .= $decodedState->origin;
            }
        }
        $facebookHelper = null;
        try {
            $facebookHelper = $this->get('fb_helper.service');
        } catch (ServiceCircularReferenceException $circularReferenceException) {
            $errorDummy = null;
        } catch (ServiceNotFoundException $notFoundException) {
            $errorDummy = null;
        } catch (Exception $unexpectedException) {
            $errorDummy = null;
        }
        if ($facebookHelper !== null) {
            $facebookRedirectLoginHelper = $facebookHelper->getRedirectLoginHelper();
            if ($requestQueryState) {
                $persistentDataHandler = $facebookRedirectLoginHelper->getPersistentDataHandler();
                if($persistentDataHandler!==null) {
                    $persistentDataHandler->set('state', $requestQueryState);
                }
            }
            if($facebookRedirectLoginHelper!==null) {
                try {
                    $accessToken = $facebookRedirectLoginHelper->getAccessToken();

                    if ($accessToken === null) {
                        $facebookRedirectLoginHelperErrorMsg = $facebookRedirectLoginHelper->getError();
                        if (!empty($facebookRedirectLoginHelperErrorMsg)) {
                            //header('HTTP/1.0 401 Unauthorized');
                            //$errorMsg = 'Error: ' . $facebookRedirectLoginHelperErrorMsg . "\n";
                            //$errorMsg .= 'Error Code: ' . $facebookRedirectLoginHelper->getErrorCode() . "\n";
                            //$errorMsg .= 'Error Reason: ' . $facebookRedirectLoginHelper->getErrorReason() . "\n";
                            //$errorMsg .= 'Error Description: ' . $facebookRedirectLoginHelper->getErrorDescription() . "\n";
                            //LogError($errorMsg);
                        } else {
                            //header('HTTP/1.0 400 Bad Request');
                            //LogError('Bad request');
                        }
                    }

                    // The OAuth 2.0 client handler helps us manage access tokens
                    $oAuth2Client = $facebookHelper->getOAuth2Client();

                    if ($oAuth2Client !== null) {
                        // Get the access token metadata from /debug_token
                        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

                        if ($tokenMetadata !== null) {
                            // Validation (these will throw FacebookSDKException's when they fail)
                            $settings = $this->get('settings');
                            if (!empty($settings)) {
                                $facebookApiId = $settings->getDomainSetting('foreignaccount_facebook_apiid');
                                $tokenMetadata->validateAppId($facebookApiId);
                            }
                            $tokenMetadata->validateExpiration();

                            if (!$accessToken->isLongLived()) {
                                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                            }

                            $facebookIdArray = $facebookHelper->getUserRelatedFacebookIdArray($accessToken);

//                            if(!empty($redirectUrl)) {
//                                $request->request->set('facebookIdArray', json_encode($facebookIdArray));
//                            }
                        }
                    }
                } catch (FacebookSDKException $facebookSDKException) {
                    $errorDummy = null;
                    //header('HTTP/1.0 400 Bad Request');
                    // When validation fails or other local issues
                    //LogError('Facebook SDK returned an error: ' . $facebookSDKException->getMessage());
                }
                catch (Exception $unexpectedException) {
                    $errorDummy = null;
                    //header('HTTP/1.0 400 Bad Request');
                    //LogError('Facebook SDK returned an unexpected error: ' . $unexpectedException->getMessage());
                }
            }
        }
        return new Response(json_encode($facebookIdArray));
        return $this->redirect($redirectUrl);
    }
}
