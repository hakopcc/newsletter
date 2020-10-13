<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdditionalVideosArticle;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\AdditionalVideosArticle\Entity\ArticleLevelFieldVideos;
use Exception;

class AdditionalVideosArticleBundle extends Bundle
{
    private $devEnvironment = false;
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
                Hooks::Register('articlecode_after_save', function (&$params = null) {
                    return $this->getArticleCodeAfterSave($params);
                });
                Hooks::Register('classarticle_before_delete', function (&$params = null) {
                    return $this->getClassArticleBeforeDelete($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    return $this->getSitemgrHeaderAfterRenderMetatags($params);
                });
                Hooks::Register('formarticle_after_imageform', function (&$params = null) {
                    return $this->getArticleFormAfterImageForm($params);
                });
                Hooks::Register('legacy_formpricing_avoid_get_form_fields', function (&$params = null) {
                    return $this->getLegacyFormpricingAvoidGetFormFields($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('articlecode_after_save', function (&$params = null) {
                    return $this->getArticleCodeAfterSave($params);
                });
                Hooks::Register('classarticle_before_delete', function (&$params = null) {
                    return $this->getClassArticleBeforeDelete($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    return $this->getSitemgrHeaderAfterRenderMetatags($params);
                });
                Hooks::Register('articlelevel_construct', function (&$params = null) {
                    return $this->getArticleLevelConstruct($params);
                });
                Hooks::Register('articlelevelfeature_before_return', function (&$params = null) {
                    return $this->getArticleLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('articledetail_after_render_gallery', function (&$params = null) {
                    return $this->getArticleDetailAfterRenderGallery($params);
                });
                Hooks::Register('formarticle_after_imageform', function (&$params = null) {
                    return $this->getArticleFormAfterImageForm($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of AdditionalVideosArticleBundle.php', ['exception' => $e]);
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

    /**
     * @param null $params
     * @throws Exception
     */
    private function getLegacyFormpricingAvoidGetFormFields(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $type = $params['type'];
                $arrayFieldsRef = &$params['array_fields'];
                if(empty($type) || $type!=='article') {
                    $params['_return'] = false;
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getLegacyFormpricingAvoidGetFormFields method of AdditionalVideosArticleBundle.php', ['exception' => $e]);
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

    private function getFormPricingAfterAddFields(&$params = null)
    {
        if ($params['type'] == 'article') {

            $translator = $this->container->get('translator');

            $params['levelOptions'][] = [
                'name'  => 'videos',
                'type'  => 'numeric',
                'title' => $translator->trans('Number of videos'),
                'tip'   => $translator->trans('How many videos can an owner add?'),
                'min'   => 0,
                'max'   => 25,
            ];
        }
    }

    private function getPaymentGatewayAfterSaveLevels(&$params = null)
    {
        if ($params['type'] == 'article' && $params['levelOptionData']['videos']) {

            $doctrine = $this->container->get('doctrine');
            $manager = $this->container->get('doctrine')->getManager();

            foreach ($params['levelOptionData']['videos'] as $level => $field) {

                $articleLevel = $doctrine->getRepository('AdditionalVideosArticleBundle:ArticleLevelFieldVideos')->findOneBy([
                    'level' => $level,
                ]);

                if ($articleLevel) {
                    $articleLevel->setField($field);
                    $manager->persist($articleLevel);
                } else {
                    $articleLevel = new ArticleLevelFieldVideos();
                    $articleLevel->setLevel($level);
                    $articleLevel->setField($field);
                    $manager->persist($articleLevel);
                }
            }

            $manager->flush();
        }
    }

    private function getFormLevelsRenderFields(&$params = null)
    {
        if (is_a($params['levelObj'], 'ArticleLevel') && $params['option']['name'] == 'videos') {

            $params['levelObj']->videos = [];

            $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalVideosArticleBundle:ArticleLevelFieldVideos')->findBy([],
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
                $('.videos-articles').trigger('change');
            });
        </script>";
    }

    private function getArticleCodeAfterSave(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();
        $article = $params['article'];

        $statement = $connection->prepare('DELETE FROM Article_Videos WHERE article_id = :article');
        $statement->bindValue('article', $article->id);
        $statement->execute();

        if (isset($_POST['video_snippet']) && is_array($_POST['video_snippet'])) {
            foreach ($_POST['video_snippet'] as $key => $snippet) {
                if ($snippet) {
                    $statement = $connection->prepare('INSERT INTO Article_Videos VALUES (NULL, :article, :snippet, :url, :description, :image_url);');
                    $statement->bindValue('article', $article->id);
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

    private function getClassArticleBeforeDelete(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Article_Videos WHERE article_id = :id');
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
                margin: 0;
                color: white;
            }

            .additional-videos .panel-heading {
                border: none;
                border-bottom: 1px solid #e6e6e6;
                font-size: 1.2em;
                color: #8d8b87;
                padding: .7em 1em .8em;
                overflow-y: hidden;
            }

            .additional-videos.panel.panel-form-media .panel-body .center-block p  {
                font-size: 1em;
            }
        </style>';
    }

    private function getArticleFormAfterImageForm(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalVideosArticleBundle:ArticleLevelFieldVideos')->findOneBy([
            'level' => isset($_GET['level']) ? $_GET['level'] : $params['article']->level,
        ]);

        $max_videos = !empty($resultLevel) ? $resultLevel->getField() : 0;

        $videos = [];
        if ($params['article']->id) {
            $statement = $connection->prepare('SELECT * FROM Article_Videos WHERE article_id = :id ORDER BY id ASC');
            $statement->bindValue('id', $params['article']->id);
            $statement->execute();

            $videos = $statement->fetchAll();
        }

        echo $this->container->get('templating')->render('AdditionalVideosArticleBundle::videopanel.html.twig', [
            'item'       => $params['article'],
            'max_videos' => $max_videos,
            'videos'     => $videos,
        ]);
    }

    private function getArticleLevelConstruct(&$params = null)
    {
        $params['that']->videoCount = 0;
    }

    private function getArticleLevelFeatureBeforeReturn(&$params = null)
    {
        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalVideosArticleBundle:ArticleLevelFieldVideos')->findOneBy([
            'level' => $params['level']->getValue(),
        ]);

        $resultLevel and $params['articleLevel']->videoCount = $resultLevel->getField();
    }

    private function getArticleDetailAfterRenderGallery(&$params = null)
    {
        if (!$params['isSample']) {

            $em = $this->container->get('doctrine')->getManager();
            $connection = $em->getConnection();

            $statement = $connection->prepare('SELECT * FROM Article_Videos WHERE article_id = :id ORDER BY id ASC');
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

            echo $this->container->get('templating')->render('AdditionalVideosArticleBundle::videodetail.html.twig', [
                'level'   => $params['level'],
                'content' => $content,
            ]);

        }
    }
}
