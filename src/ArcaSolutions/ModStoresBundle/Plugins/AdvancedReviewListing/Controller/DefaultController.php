<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function galleryItemAction(Request $request)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];

        switch ($request->get('action', '')) {

            case 'remove':

                if ($galleryId = $request->get('galleryId', '')) {

                    $doctrine = $this->container->get('doctrine');
                    $manager = $doctrine->getManager();

                    $gallery = $doctrine->getRepository('ImageBundle:GalleryImage')->find($galleryId);

                    if (!empty($gallery)) {
                        $image = $gallery->getImage();

                        $imagePath = $this->container->get('templating.helper.assets')
                            ->getUrl($this->container->get('imagehandler')->getPath($image), 'domain_images');

                        $manager->remove($gallery);
                        $manager->flush();

                        $manager->remove($image);
                        $manager->flush();
                    }

                    @unlink($this->container->get('kernel')->getRootDir().'/../web'.$imagePath);
                    $this->container->get('liip_imagine.cache.manager')->remove($imagePath,
                        array_keys($this->container->getParameter('liip_imagine.filter_sets')));

                    $response = [
                        'success' => true,
                        'message' => '',
                    ];
                }
                break;

        }

        return JsonResponse::create($response);
    }
}
