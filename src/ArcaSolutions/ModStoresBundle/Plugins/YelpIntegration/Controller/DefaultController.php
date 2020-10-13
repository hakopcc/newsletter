<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkYelpKeyAction(Request $request)
    {
        $yelp = $this->container->get('api.yelp');
        
        $yelp->setYelpKey($request->request->get('yelpKey'));
        
        try {
            $response = $yelp->search(['term' => 'Yelp', 'location' => '140 New Montgomery']);
            
            if (is_array($response) && is_array($response['businesses'])) {
                $data = ['validKey' => true];
            } else {
                $data = ['validKey' => false];
            }
        } catch (Exception $e) {
            $data = ['validKey' => false];
        }
        
        return new JsonResponse($data);
    }
    
    
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkTimezoneDbKeyAction(Request $request)
    {
        $timezoneDb = $this->container->get('api.timezonedb');
    
        $timezoneDb->setTimezoneDbKey($request->request->get('timezoneDbKey'));
        
        try {
            $response = $timezoneDb->search(['term' => 'Yelp', 'location' => '140 New Montgomery']);
            
            if (is_array($response) && is_array($response['businesses'])) {
                $data = ['validKey' => true];
            } else {
                $data = ['validKey' => false];
            }
        } catch (Exception $e) {
            $data = ['validKey' => false];
        }
        
        return new JsonResponse($data);
    }
}
