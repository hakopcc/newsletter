<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\BrowseMapListing;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;

class BrowseMapListingBundle extends Bundle
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
                Hooks::Register('generalsettings_after_save', function (&$params = null) {
                    return $this->getGeneralSettingsAfterSave($params);
                });
                Hooks::Register('generalsettings_after_render_form', function (&$params = null) {
                    return $this->getGeneralSettingsAfterRenderForm($params);
                });
                Hooks::Register('widget_construct', function (&$params = null) {
                    return $this->getWidgetConstruct($params);
                });


            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('widget_construct', function (&$params = null) {
                    return $this->getWidgetConstruct($params);
                });
                Hooks::Register('dailymaintenance_after_load_configurations', function (&$params = null) {
                    return $this->getDailyMaintenanceAfterLoadConfigurations($params);
                });
                Hooks::Register('wysiwygextension_before_validate_widget', function (&$params = null) {
                    return $this->getWysiwygExtensionBeforeValidateWidget($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of BrowseMapListingBundle.php', ['exception' => $e]);
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

    private function getGeneralSettingsAfterSave(&$params = null)
    {
        if(!empty($params['http_post_array']) && is_array($params['http_post_array']) && array_key_exists('save_plugin',$params['http_post_array'])) {

            $settings = $this->container->get('settings');

            $settings->setSetting('browsebymap_colorcoded', $_POST['browsebymap_colorcoded']);
            $settings->setSetting('browsebymap_map', $_POST['browsebymap_map']);
            $settings->setSetting('browsebymap_jsMap','jquery-jvectormap-' . str_replace('_', '-', $_POST['browsebymap_map']) . '.js');
            $settings->setSetting('browsebymap_mapBackgroundColor', $_POST['browsebymap_mapBackgroundColor']);
            $settings->setSetting('browsebymap_mapColor', $_POST['browsebymap_mapColor']);
            $settings->setSetting('browsebymap_mapColorRangeStart', str_replace('#', '', $this->container->get('browsemaplisting.service')->getHexColorDiff($_POST['browsebymap_mapColor'], 64)));
            $settings->setSetting('browsebymap_mapColorRangeEnd', str_replace('#', '', $this->container->get('browsemaplisting.service')->getHexColorDiff($_POST['browsebymap_mapColor'], -64)));


            $domain = $this->container->get('doctrine')->getRepository('CoreBundle:Domain','main')->find(SELECTED_DOMAIN_ID);
            $info['domainDBName'] = $domain->getDatabasename();

            $this->container->get('browsemaplisting.service')->insertLocationRelated($info, 1);
            $this->container->get('browsemaplisting.service')->insertLocationRelated($info, 3);
            $this->container->get('browsemaplisting.service')->updateLocationRelated();
            $this->container->get('browsemaplisting.service')->generateDataJson();

            $params['success'] = true;
        }
    }

    private function getGeneralSettingsAfterRenderForm(&$params = null)
    {
        $settings = $this->container->get('settings');

        $mapColor = $settings->getDomainSetting('browsebymap_mapColor',
            true) ? $settings->getDomainSetting('browsebymap_mapColor') : '164378';
        $backgroundColor = $settings->getDomainSetting('browsebymap_mapBackgroundColor',
            true) ? $settings->getDomainSetting('browsebymap_mapBackgroundColor') : 'FFFFFF';
        $map = $settings->getDomainSetting('browsebymap_map',
            true) ? $settings->getDomainSetting('browsebymap_map') : 'world_mill';

        echo $this->container->get('templating')->render('BrowseMapListingBundle::browse_settings_form.html.twig', [
            'action'                         => $_SERVER['PHP_SELF'],
            'error'                          => $params['error'],
            'maps'                           => $this->container->get('browsemaplisting.service')->getMapLocationOptions(),
            'message_header'                 => $params['message_header'],
            'browsebymap_colorcoded'         => $settings->getDomainSetting('browsebymap_colorcoded', true),
            'browsebymap_map'                => $map,
            'browsebymap_jsMap'              => $settings->getDomainSetting('browsebymap_jsMap', true),
            'browsebymap_mapBackgroundColor' => $backgroundColor,
            'browsebymap_mapColor'           => $mapColor,
            'browsebymap_mapColorRangeStart' => $settings->getDomainSetting('browsebymap_mapColorRangeStart', true),
            'browsebymap_mapColorRangeEnd'   => $settings->getDomainSetting('browsebymap_mapColorRangeEnd', true),
        ]);
    }

    private function getWidgetConstruct(&$params = null)
    {
        $params['widgetNonDuplicate']['browsebymap'][] = 'Browse By Map';
    }

    private function getDailyMaintenanceAfterLoadConfigurations(&$params = null)
    {
        $params['messageLog'] = 'Update LocationRelated_1 and LocationRelated_3 table';

        $this->container->get('browsemaplisting.service')->insertLocationRelated($params, 1);
        $this->container->get('browsemaplisting.service')->insertLocationRelated($params, 3);
        $this->container->get('browsemaplisting.service')->updateLocationRelated();
        $this->container->get('browsemaplisting.service')->generateDataJson();
    }

    private function getWysiwygExtensionBeforeValidateWidget(&$params = null)
    {
        if ($params['widgetFile'] == '::widgets/page-editor/map/browse-by-map.html.twig') {
            $settings = $this->container->get('settings');

            $jsHandler = $this->container->get('javascripthandler');

            $jsHandler->addTwigParameter('browsebymap_colorcoded',
                $settings->getDomainSetting('browsebymap_colorcoded'));
            $jsHandler->addTwigParameter('browsebymap_map',
                $settings->getDomainSetting('browsebymap_map') ? $settings->getDomainSetting('browsebymap_map') : 'world_mill');
            $jsHandler->addTwigParameter('browsebymap_mapColor',
                $settings->getDomainSetting('browsebymap_mapColor') ? $settings->getDomainSetting('browsebymap_mapColor') : '164378');
            $jsHandler->addTwigParameter('browsebymap_mapBackgroundColor',
                $settings->getDomainSetting('browsebymap_mapBackgroundColor') ? $settings->getDomainSetting('browsebymap_mapBackgroundColor') : 'FFFFFF');
            $jsHandler->addTwigParameter('browsebymap_mapColorRangeStart',
                $settings->getDomainSetting('browsebymap_mapColorRangeStart'));
            $jsHandler->addTwigParameter('browsebymap_mapColorRangeEnd',
                $settings->getDomainSetting('browsebymap_mapColorRangeEnd'));

            $jsHandler->addJSExternalFile('assets/js/utility/cache.js');
            $jsHandler->addJSExternalFile('assets/js/utility/miscellaneous.js');
            $jsHandler->addJSExternalFile('bundles/browsemaplisting/js/jquery-jvectormap-2.0.3.min.js');
            $jsHandler->addJSExternalFile('bundles/browsemaplisting/js/'.($settings->getDomainSetting('browsebymap_jsMap') ? $settings->getDomainSetting('browsebymap_jsMap') : 'jquery-jvectormap-world-mill.js'));

            $jsHandler->addJSBlock('BrowseMapListingBundle::js/browsebymap.html.twig');

            $params['widgetFile'] = 'BrowseMapListingBundle::browse_by_map_content.html.twig';
        }
    }
}
