<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * Newsletter action
     *
     * Used to save news visitors
     *
     * @return JsonResponse
     */
    public function geoboundAction()
    {
        $tailoredMap = $this->get('tailoredplacement.map');
        $parameterHandler = $this->get('search.parameters');

        $coordinates[0] = $_POST['top_left'];
        $coordinates[1] = $_POST['bottom_right'];

        $avoid_ids = isset($_POST['item_ids']) ? $_POST['item_ids'] : [];

        $location[0] = $coordinates[0][0];
        $location[1] = $coordinates[0][1];

        $where = implode(' ', $parameterHandler->getWheres());

        $data = $tailoredMap->searchBounds($coordinates, 'listing', $avoid_ids, $where);

        return new JsonResponse($data);
    }
}
