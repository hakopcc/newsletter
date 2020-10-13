<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ChatIntegration;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;

class ChatIntegrationBundle extends Bundle
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
                Hooks::Register('generalsettings_after_render_form', function (&$params = null) {
                    return $this->getGeneralSettingsAfterRenderForm($params);
                });
                Hooks::Register('generalsettings_after_save', function (&$params = null) {
                    return $this->getGeneralSettingsAfterSave($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('base_after_add_js', function (&$params = null) {
                    return $this->getBaseAfterJs($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of ChatIntegrationBundle.php', ['exception' => $e]);
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

    private function getGeneralSettingsAfterRenderForm(&$params = null)
    {
        echo $this->container->get('templating')->render('ChatIntegrationBundle::sitemgr-form.html.twig', [
            'embed' => $this->container->get('settings')->getDomainSetting('chatintegration_embed', true),
        ]);
    }

    private function getGeneralSettingsAfterSave(&$params = null)
    {
        if(!empty($params['http_post_array']) && is_array($params['http_post_array']) && array_key_exists('save_plugin',$params['http_post_array'])) {
            $embed = $_POST['chatintegration_embed'];

            if (!empty($embed)) {
                $embed = rtrim(ltrim($embed));

                if (!preg_match('/<script[^>]*>/', $embed)) {
                    $embed = "<script>\r\n" . $embed;
                }

                if (!preg_match("/<\/script>/", $embed)) {
                    $embed .= "\r\n</script>";
                }
            }

            $this->container->get('settings')->setSetting('chatintegration_embed', $embed);
        }
    }

    private function getBaseAfterJs(&$params = null)
    {
        echo $this->container->get('settings')->getDomainSetting('chatintegration_embed');
    }
}
