<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\BrowseMapListing\Controller;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function dataAction()
    {
        $baseFolder = $this->getParameter('kernel.root_dir').'/../web';
        $tmpFolder = $baseFolder.'/custom/domain_'.$this->get('multi_domain.information')->getId().'/tmp/';

        if (!is_dir($tmpFolder) && !mkdir($tmpFolder, 0777, true)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created',
                $tmpFolder));
        }

        $filename = $tmpFolder.'/jvectormap_data.json';

        if (!file_exists($filename)) {
            $handle = fopen($filename, 'wb');
            fwrite($handle, json_encode(['amount' => null, 'locations' => null]));
            fclose($handle);
        }

        return new Response(file_get_contents($filename));
    }
}
