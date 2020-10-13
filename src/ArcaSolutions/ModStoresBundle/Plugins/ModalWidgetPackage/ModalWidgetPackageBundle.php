<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ModalWidgetPackage;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ModalWidgetPackageBundle extends Bundle
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
                Hooks::Register('widget_construct', function (&$params = null) {
                    return $this->getWidgetConstruct($params);
                });
                Hooks::Register('widgetactionajax_after_load', function (&$params = null) {
                    return $this->getWidgetActionAjaxAfterLoad($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('widget_construct', function (&$params = null) {
                    return $this->getWidgetConstruct($params);
                });
                Hooks::Register('wysiwygextension_before_validate_widget', function (&$params = null) {
                    return $this->getWysiwygExtensionBeforeValidateWidget($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of ModalWidgetPackageBundle.php', ['exception' => $e]);
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

    private function getWidgetConstruct(&$params = null)
    {
        $params['widgetNonDuplicate']['drawer_popup'] = [
            'Pop-up Modal',
            'Left Drawer',
            'Right Drawer',
            'Top Drawer',
            'Bottom Drawer',
        ];
    }

    private function getWidgetActionAjaxAfterLoad(&$params = null)
    {
        $request = Request::createFromGlobals();

        if (
            $request->get('modalFullName') == 'edit-custom-popup-content-modal'
            || $request->get('modalFullName') == 'edit-left-drawer-content-modal'
            || $request->get('modalFullName') == 'edit-right-drawer-content-modal'
            || $request->get('modalFullName') == 'edit-top-drawer-content-modal'
            || $request->get('modalFullName') == 'edit-bottom-drawer-content-modal'
        ) {

            $widget = $this->container->get('widget.service');
            $pageWidget = $this->container->get('pagewidget.service');

            $originalWidget = $widget->getOriginalWidget($request->get('widgetId'));
            $pageWidget = $pageWidget->getWidgetFromPage($request->get('pageWidgetId'));

            if (null !== $originalWidget) {
                $originalWidget->setContent(json_decode($originalWidget->getContent(), true));
            }

            if (null !== $pageWidget) {
                $pageWidget->setContent(json_decode($pageWidget->getContent(), true));
            }

            echo $this->container->get('templating')->render('ModalWidgetPackageBundle::sitemgr-popup-modal.html.twig',
                [
                    'originalWidget' => $originalWidget,
                    'pageWidget'     => $pageWidget,
                ]);

            unset($_GET['action']);
            $params['returnArray'] = [];
        }
    }

    private function getWysiwygExtensionBeforeValidateWidget(&$params = null)
    {
        $request = Request::createFromGlobals();

        $closedModals = [];
        if ($request->cookies->has('closed_modals')) {
            $closedModals = json_decode($request->cookies->get('closed_modals'));
        }

        switch ($params['widgetFile']) {

            case '::widgets/page-editor/modal/popup.html.twig':
                !in_array('popup',
                    $closedModals) and $params['widgetFile'] = 'ModalWidgetPackageBundle::popup.html.twig';
                break;

            case '::widgets/page-editor/modal/left-drawer.html.twig':
                !in_array('left',
                    $closedModals) and $params['widgetFile'] = 'ModalWidgetPackageBundle::left-drawer.html.twig';
                break;

            case '::widgets/page-editor/modal/right-drawer.html.twig':
                !in_array('right',
                    $closedModals) and $params['widgetFile'] = 'ModalWidgetPackageBundle::right-drawer.html.twig';
                break;

            case '::widgets/page-editor/modal/top-drawer.html.twig':
                !in_array('top',
                    $closedModals) and $params['widgetFile'] = 'ModalWidgetPackageBundle::top-drawer.html.twig';
                break;

            case '::widgets/page-editor/modal/bottom-drawer.html.twig':
                !in_array('bottom',
                    $closedModals) and $params['widgetFile'] = 'ModalWidgetPackageBundle::bottom-drawer.html.twig';
                break;

        }
    }
}
