<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdditionalVideosListing;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\AdditionalVideosListing\Entity\ListingLevelFieldVideos;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

class AdditionalVideosListingBundle extends Bundle
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
            $translator->trans('videoCount', array(), 'advertise');
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
                Hooks::Register('paymentgateway_after_save_levels', function (&$params = null) {
                    return $this->getPaymentGatewayAfterSaveLevels($params);
                });
                Hooks::Register('formlevels_render_fields', function (&$params = null) {
                    return $this->getFormLevelsRenderFields($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    return $this->getListingCodeAfterSave($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    return $this->getSitemgrHeaderAfterRenderMetatags($params);
                });
                Hooks::Register('listingform_overwrite_video', function (&$params = null) {
                    return $this->getListingFormOverwriteVideo($params);
                });
                Hooks::Register('classlisting_before_preparesave', function (&$params = null) {
                    return $this->getClassListingBeforePrepareSave($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('api_before_returnlisting', function (&$params = null) {
                    return $this->getApiBeforeReturnListing($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    return $this->getListingCodeAfterSave($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                    return $this->getListingLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('modulelevelapi_before_return_listingmap', function (&$params = null) {
                    return $this->getModuleLevelApiBeforeReturnListingMap($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    return $this->getSitemgrHeaderAfterRenderMetatags($params);
                });
                Hooks::Register('listingdetail_overwrite_video', function (&$params = null) {
                    return $this->getListingDetailOverwriteVideo($params);
                });
                Hooks::Register('listingform_overwrite_video', function (&$params = null) {
                    return $this->getListingFormOverwriteVideo($params);
                });
                Hooks::Register('detailextension_overwrite_hasvideo', function (&$params = null) {
                    return $this->getDetailExtensionOverwriteHasvideo($params);
                });
            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of AdditionalVideosListingBundle.php', ['exception' => $e]);
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

    private function getFormPricingAfterAddFields(&$params = null)
    {
        if ($params['type'] == 'listing') {

            $translator = $this->container->get('translator');

            $params['levelOptions'][] = [
                'name'  => 'videos',
                'type'  => 'numeric',
                'title' => $translator->trans('Number of videos'),
                'tip'   => $translator->trans('How many videos can an owner add?'),
                'min'   => 0,
                'max'   => 25,
            ];

            for ($i = 0, $iMax = count($params['levelOptions']); $i < $iMax; $i++) {
                if ($params['levelOptions'][$i]['name'] == 'video') {
                    unset($params['levelOptions'][$i]);
                }
            }
        }
    }

    private function getPaymentGatewayAfterSaveLevels(&$params = null)
    {
        if ($params['type'] == 'listing' && $params['levelOptionData']['videos']) {

            $doctrine = $this->container->get('doctrine');
            $manager = $this->container->get('doctrine')->getManager();

            foreach ($params['levelOptionData']['videos'] as $level => $field) {

                $listingLevel = $doctrine->getRepository('AdditionalVideosListingBundle:ListingLevelFieldVideos')->findOneBy([
                    'level' => $level,
                ]);

                if ($listingLevel) {
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                } else {
                    $listingLevel = new ListingLevelFieldVideos();
                    $listingLevel->setLevel($level);
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                }
            }

            $manager->flush();

        }
    }

    private function getFormLevelsRenderFields(&$params = null)
    {
        if (is_a($params['levelObj'], 'ListingLevel') && $params['option']['name'] == 'videos') {

            $params['levelObj']->videos = [];

            $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalVideosListingBundle:ListingLevelFieldVideos')->findBy([],
                ['level' => 'DESC']);

            if ($resultLevel) {
                foreach ($resultLevel as $levelfield) {
                    $params['levelObj']->videos[] = $levelfield->getField();
                }
            }
        }
    }

    private function getModulesFooterAfterRenderJs(&$params = null)
    {
        echo "<script type='text/javascript'>
            $(document).ready(function(){
                $('.videos').trigger('change');
            });
        </script>";
    }

    private function getListingCodeAfterSave(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Listing_Videos WHERE listing_id = :listing');
        $statement->bindValue('listing', $params['listing']->id);
        $statement->execute();

        if (isset($_POST['video_snippet']) && is_array($_POST['video_snippet'])) {
            foreach ($_POST['video_snippet'] as $key => $snippet) {
                if ($snippet) {
                    $statement = $connection->prepare('INSERT INTO Listing_Videos VALUES (NULL, :listing, :snippet, :url, :description, :image_url);');
                    $statement->bindValue('listing', $params['listing']->id);
                    $statement->bindValue('snippet', $snippet);
                    $statement->bindValue('url', $_POST['video_url'][$key]);
                    $statement->bindValue('description', $_POST['video_description'][$key]);
                    $statement->bindValue('image_url',
                        system_getVideoiFrame($_POST['video_url'][$key], 380, null, true));
                    $statement->execute();
                }
            }
        }
    }

    private function getClassListingBeforeDelete(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Listing_Videos WHERE listing_id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();
    }

    private function getSitemgrHeaderAfterRenderMetatags(&$params = null)
    {
        echo '<style>
            #videosBody iframe{
                width: 100%;
            }

            #videosBody .delete-button {
                padding: 3px 9px 3px 9px;
            }

            #videosBody .delete-button i{
                font-size: 22px;
                margin-bottom: 0;
            }

            .additional-videos.panel.panel-form-media .panel-body .center-block p  {
                font-size: 1em;
            }
        </style>';
    }

    private function getListingFormOverwriteVideo(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalVideosListingBundle:ListingLevelFieldVideos')->findOneBy([
            'level' => isset($_GET['level']) ? $_GET['level'] : $params['listing']->level,
        ]);

        $max_videos = !empty($resultLevel) && $resultLevel->getField() ? $resultLevel->getField() : 0;

        $videos = [];
        if ($params['listing']->id) {
            $statement = $connection->prepare('SELECT * FROM Listing_Videos WHERE listing_id = :id ORDER BY id ASC');
            $statement->bindValue('id', $params['listing']->id);
            $statement->execute();

            $videos = $statement->fetchAll();
        }

        echo $this->container->get('templating')->render('AdditionalVideosListingBundle::videopanel.html.twig', [
            'item'       => $params['listing'],
            'max_videos' => $max_videos,
            'videos'     => $videos,
        ]);
    }

    private function getClassListingBeforePrepareSave(&$params = null)
    {
        $params['that']->video_snippet = '';
        $params['that']->video_url = '';
        $params['that']->video_description = '';
    }

    private function getApiBeforeReturnListing(&$params = null)
    {
        $video = $this->container->get('doctrine')->getRepository('AdditionalVideosListingBundle:ListingVideos')->findBy([
            'listing' => $params['listing']->getId(),
        ]);

        $videoUrl = '';
        $videoSnippet = '';
        $videoDescription = '';

        if ($video) {
            $videoUrl = $video[0]->getVideoUrl();
            $videoSnippet = $video[0]->getVideoSnippet();
            $videoDescription = $video[0]->getVideoDescription();
        }

        $params['listing']->setVideoUrl($videoUrl);
        $params['listing']->setVideoSnippet($videoSnippet);
        $params['listing']->setVideoDescription($videoDescription);
    }

    private function getListingLevelFeatureBeforeReturn(&$params = null)
    {
        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalVideosListingBundle:ListingLevelFieldVideos')->findOneBy([
            'level' => $params['level']->getValue(),
        ]);

        $resultLevel and $params['listingLevel']->videoCount = $resultLevel->getField();

        unset($params['listingLevel']->hasVideo);
    }

    private function getModuleLevelApiBeforeReturnListingMap(&$params = null)
    {
        unset($params['levelItems']['video']);
    }

    private function getListingDetailOverwriteVideo(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Listing_Videos WHERE listing_id = :id ORDER BY id ASC');
        $statement->bindValue('id', $params['item']->getId());
        $statement->execute();

        $videos = $statement->fetchAll();

        $content = [
            'labelVideoGallery' => 'Video Gallery',
            'dataColumn'        => '2',
            'backgroundColor'   => 'base',
        ];

        foreach ($videos as $video) {
            $content['videos'][] = [
                'url'         => $video['video_url'],
                'description' => $video['video_description'],
                'iframe'      => $video['video_snippet'],
                'imageUrl'    => $video['video_image_url'],
            ];
        }

        echo $this->container->get('templating')->render('AdditionalVideosListingBundle::videodetail.html.twig', [
            'level'   => $params['level'],
            'content' => $content,
        ]);
    }

    private function getDetailExtensionOverwriteHasvideo(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT id FROM Listing_Videos WHERE listing_id = :id ORDER BY id ASC');
        $statement->bindValue('id', $params['listing']->getId());
        $statement->execute();

        $videos = $statement->fetchAll();

        if ($params['listingLevel']->videoCount > 0 && !empty($videos)) {
            $params['contentCount']++;
            $params['overviewCount']++;
        }
    }
}
