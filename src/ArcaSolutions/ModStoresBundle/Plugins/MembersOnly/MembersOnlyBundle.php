<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MembersOnly;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;

class MembersOnlyBundle extends Bundle
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

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('base_after_add_js', function (&$params = null) {
                    return $this->getBaseAfterJs($params);
                });
                Hooks::Register('base_before_close_body', function (&$params = null) {
                    return $this->getBaseBeforeCloseBody($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of MembersOnlyBundle.php', ['exception' => $e]);
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

            $this->container->get('settings')->setSetting('blockUsersOnlyTitle', trim($_POST['blockUsersOnlyTitle']));
            $this->container->get('settings')->setSetting('blockUsersOnlyDescription',
                trim($_POST['blockUsersOnlyDescription']));
            $this->container->get('settings')->setSetting('blockUsersOnlyCheckbox', trim($_POST['blockUsersOnlyCheckbox']));
            $this->container->get('settings')->setSetting('blockUsersOnlyDropdown', trim($_POST['blockUsersOnlyDropdown']));

            $params['success'] = true;
        }
    }

    private function getGeneralSettingsAfterRenderForm(&$params = null)
    {
        $translator = $this->container->get('translator');
        $settings = $this->container->get('settings');

        $levelDropdown = html_selectBox(
            'blockUsersOnlyDropdown',
            [
                $translator->trans('Sponsors Only'),
                $translator->trans('Sponsors + Site Visitor'),
            ],
            [
                1,
                2,
            ],
            $settings->getDomainSetting('blockUsersOnlyDropdown', true),
            '',
            'class="form-control status-select"',
            ''
        );

        $modalContent = $settings->getDomainSetting('blockUsersOnlyDescription', true);

        $contentEditor = system_addCKEditor(
            'blockUsersOnlyDescription',
            $modalContent,
            30,
            15,
            $this->container->get('settings')->getSetting('sitemgr_language'),
            '',
            true,
            true,
            false,
            true
        );

        echo $this->container->get('templating')->render('MembersOnlyBundle::sitemgr-form-members-only.html.twig', [
            'blockUsersOnlyCheckbox'    => $settings->getDomainSetting('blockUsersOnlyCheckbox', true),
            'blockUsersOnlyTitle'       => $settings->getDomainSetting('blockUsersOnlyTitle', true),
            'blockUsersOnlyDropdown'    => $levelDropdown,
            'blockUsersOnlyDescription' => $contentEditor,
        ]);
    }

    private function getBaseAfterJs(&$params = null)
    {
        $customCheckbox = $this->container->get('settings')->getDomainSetting('blockUsersOnlyCheckbox');
        $customDropdown = $this->container->get('settings')->getDomainSetting('blockUsersOnlyDropdown');

        $destiny = $this->container->get('request_stack')->getCurrentRequest()->getRequestUri();

        if ($customDropdown == 1) {
            $customRouteLogin = '/sponsors/login.php?destiny='.urlencode($destiny);
            $customRouteBecomeMember = '/'.$this->container->getParameter('alias_advertise_url_divisor');
        } else {
            $customRouteLogin = '/profile/login.php?destiny='.urlencode($destiny);
            $customRouteBecomeMember = '/profile/add.php';
        }

        $hasUser = false;
        $isSponsor = false;
        if ($userLoggedIn = $this->container->get('user')->getUser()) {
            $hasUser = $userLoggedIn->getAccountId();
            if($hasUser) {
                $account = $this->container->get('doctrine')->getRepository('CoreBundle:Account', 'main')->find($hasUser);
                $isSponsor = ($account->getIsSponsor()==='y');
            }
        }

        $isSitemgr = false;
        if (!empty($this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SM_LOGGEDIN'))) {
            $isSitemgr = true;
        }

        $advertisePage = false;
        if ($this->container->get('request_stack')->getCurrentRequest()->getRequestUri() == '/'.$this->container->getParameter('alias_advertise_url_divisor')) {
            $advertisePage = true;
        }

        if($customCheckbox == 'on'  && !$isSitemgr && (($customDropdown == 1 && $hasUser && !($isSponsor || $advertisePage))||(!$hasUser && !$advertisePage))) {
            echo $this->container->get('twig')->render('MembersOnlyBundle::member_js.html.twig', [
                'customRouteLogin' => $customRouteLogin,
                'customRouteBecomeMember' => $customRouteBecomeMember,
            ]);
        }
    }

    private function getBaseBeforeCloseBody(&$params = null)
    {
        $customCheckbox = $this->container->get('settings')->getDomainSetting('blockUsersOnlyCheckbox');
        $customDropdown = $this->container->get('settings')->getDomainSetting('blockUsersOnlyDropdown');

        $hasUser = false;
        $isSponsor = false;
        if ($userLoggedIn = $this->container->get('user')->getUser()) {
            $hasUser = $userLoggedIn->getAccountId();
            if($hasUser) {
                $account = $this->container->get('doctrine')->getRepository('CoreBundle:Account', 'main')->find($hasUser);
                $isSponsor = ($account->getIsSponsor()==='y');
            }
        }

        $isSitemgr = false;
        if (!empty($this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SM_LOGGEDIN'))) {
            $isSitemgr = true;
        }

        $advertisePage = false;
        if ($this->container->get('request_stack')->getCurrentRequest()->getRequestUri() == '/'.$this->container->getParameter('alias_advertise_url_divisor')) {
            $advertisePage = true;
        }

        if($customCheckbox == 'on'  && !$isSitemgr && (($customDropdown == 1 && $hasUser && !($isSponsor || $advertisePage))||(!$hasUser && !$advertisePage))) {
            echo $this->container->get('twig')->render('MembersOnlyBundle::member_block.html.twig', [
                'customTitle'       => $this->container->get('settings')->getDomainSetting('blockUsersOnlyTitle'),
                'customDescription' => $this->container->get('settings')->getDomainSetting('blockUsersOnlyDescription'),
                'becomeMemberTitle' => ($customDropdown == 1 && $hasUser && !$isSponsor)?$this->container->get('translator')->trans("Become a Sponsor"):$this->container->get('translator')->trans("Become a Member")
            ]);
        }
    }
}
