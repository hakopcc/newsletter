<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MarketSelection\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setMarketAction(Request $request)
    {
        $response = new Response();

        if (isset($_POST['force_location'])) {
            if ($_POST['force_location']) {
                if ($_POST['location_id'] == 0 || $_POST['location_level'] == 0) {
                    $response->headers->setCookie(new Cookie('force_market_location', 'clean'));
                } else {
                    $response->headers->setCookie(new Cookie('force_market_location', 'true'));
                }
            } else {
                $response->headers->setCookie(new Cookie('force_market_location', 'false'));
            }
        }

        if ($_POST['location_id'] != 0 || $_POST['location_level'] != 0) {
            $response->headers->setCookie(new Cookie('market_location_id', $_POST['location_id']));
            $response->headers->setCookie(new Cookie('market_location_level', $_POST['location_level']));
        } else {
            $response->headers->setCookie(new Cookie('market_location_id', ''));
            $response->headers->setCookie(new Cookie('market_location_level', ''));
        }

        $response->sendHeaders();

        return JsonResponse::create(['updated' => 'y']);
    }
}
