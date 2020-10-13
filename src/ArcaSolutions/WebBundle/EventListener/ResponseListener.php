<?php


namespace ArcaSolutions\WebBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseListener
{
    private $isLoggedIn = false;
    private $isLoggedInSet = false;

    public function loggedIn(bool $loggedIn){
        $this->isLoggedIn = $loggedIn;
        $this->isLoggedInSet = true;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if($this->isLoggedInSet) {
            $response = $event->getResponse();
            $responseCookiesArray = $response->headers->getCookies();
            $request = $event->getRequest();
            $requestCookiesBag = $request->cookies;
            $xEdLoggedInCookieValueToSet = $this->isLoggedIn ? 'yes' : 'no';
            $xEdLoggedInExistsOnResponse = array_key_exists('X-ED-LoggedIn', $responseCookiesArray);
            if (!$xEdLoggedInExistsOnResponse && !$requestCookiesBag->has('X-ED-LoggedIn')) {
                $xEdLoggedInCookie = new Cookie('X-ED-LoggedIn', $xEdLoggedInCookieValueToSet);
                $response->headers->setCookie($xEdLoggedInCookie);
                unset($xEdLoggedInCookie);
            } else {
                /**  @var Cookie $xEdLoggedInCookie */
                $xEdLoggedInCookieValue = $xEdLoggedInExistsOnResponse ? $responseCookiesArray['X-ED-LoggedIn']->getValue() : $requestCookiesBag->get('X-ED-LoggedIn');
                if ($xEdLoggedInCookieValue !== $xEdLoggedInCookieValueToSet) {
                    if ($xEdLoggedInExistsOnResponse) {
                        $response->headers->removeCookie('X-ED-LoggedIn');
                    }
                    $xEdLoggedInCookie = new Cookie('X-ED-LoggedIn', $xEdLoggedInCookieValueToSet);
                    $response->headers->setCookie($xEdLoggedInCookie);
                    unset($xEdLoggedInCookie);
                }
                unset($xEdLoggedInCookieValue);
            }
            $event->setResponse($response);
            unset($response, $request, $requestCookiesBag, $responseCookiesArray, $xEdLoggedInCookieValueToSet, $xEdLoggedInExistsOnResponse);
        }
    }
}
