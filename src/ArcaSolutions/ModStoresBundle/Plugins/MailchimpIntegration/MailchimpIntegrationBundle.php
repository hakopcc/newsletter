<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MailchimpIntegration;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;

class MailchimpIntegrationBundle extends Bundle
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
                Hooks::Register('loadconfig_after_load', function (&$params = null) {
                    return $this->getLoadConfigAfterLoad($params);
                });
                Hooks::Register('constants_avoid_mail_app_defines', function (&$params = null) {
                    return $this->getConstantsAvoidMailAppDefines($params);
                });
                Hooks::Register('newsletter_before_add_widgettype', function (&$params = null) {
                    return $this->getNewsletterBeforeAddWidgetType($params);
                });
                Hooks::Register('newsletter_before_render_js', function (&$params = null) {
                    return $this->getNewsletterBeforeRenderJs($params);
                });
                Hooks::Register('sitemgrheader_after_render_metatags', function (&$params = null) {
                    return $this->getSitemgrHeaderAfterRenderMetatags($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('accountcode_after_save', function (&$params = null) {
                    return $this->getAccountCodeAfterSave($params);
                });
                Hooks::Register('orderlisting_before_redirect', function (&$params = null) {
                    return $this->getOrderListingBeforeRedirect($params);
                });
                Hooks::Register('orderarticle_before_redirect', function (&$params = null) {
                    return $this->getOrderArticleBeforeRedirect($params);
                });
                Hooks::Register('orderbanner_before_redirect', function (&$params = null) {
                    return $this->getOrderBannerBeforeRedirect($params);
                });
                Hooks::Register('orderclassified_before_redirect', function (&$params = null) {
                    return $this->getOrderClassifiedBeforeRedirect($params);
                });
                Hooks::Register('orderevent_before_redirect', function (&$params = null) {
                    return $this->getOrderEventBeforeRedirect($params);
                });
                Hooks::Register('loadconfig_after_load', function (&$params = null) {
                    return $this->getLoadConfigAfterLoad($params);
                });
                Hooks::Register('constants_avoid_mail_app_defines', function (&$params = null) {
                    return $this->getConstantsAvoidMailAppDefines($params);
                });
                Hooks::Register('formsignup_after_render_newsletter', function (&$params = null) {
                    return $this->getFormSignupAfterRenderNewsletter($params);
                });
                Hooks::Register('base_before_close_head', function (&$params = null) {
                    return $this->getBaseBeforeCloseHead($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of MailchimpIntegrationBundle.php', ['exception' => $e]);
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

            $settings->setSetting('mailchimp_apikey', $_POST['mailchimp_apikey']);
            $settings->setSetting('mailchimp_listid', $_POST['mailchimp_apikey'] ? $_POST['mailchimp_listid'] : '');

            $params['success'] = true;
        }
    }

    private function getGeneralSettingsAfterRenderForm(&$params = null)
    {
        $settings = $this->container->get('settings');

        $mcList = [];
        $lists = $this->container->get('mailchimp.service')->getLists();

        if (isset($lists['total_items']) && $lists['total_items'] > 0) {
            foreach ($lists['lists'] as $key => $item) {
                $mcList[$item['id']] = $item['name'];
            }
        }

        $mailchimp_list = html_selectBox(
            'mailchimp_listid',
            array_values($mcList),
            array_keys($mcList),
            $settings->getDomainSetting('mailchimp_listid', true),
            '',
            'class="form-control status-select"',
            $this->container->get('translator')->trans('Select a list')
        );

        echo $this->container->get('templating')->render('MailchimpIntegrationBundle::form_settings.html.twig', [
            'mailchimp_apikey' => $settings->getDomainSetting('mailchimp_apikey', true),
            'mailchimp_list'   => $mailchimp_list,
        ]);
    }

    private function getBaseBeforeCloseHead(&$params = null)//TODO: Remove that when a new Hook has been created on basecode twig located on the app/Resources/views/widgets/newsletter/signup-for-our-newsletter.html.twig, that consider mailchimp.service isSetup instead check if setting arcamailer_customer_listid
    {
        if ($this->container->get('mailchimp.service')->isSetUp()) {
            if(!$this->container->get('settings')->getDomainSetting('arcamailer_customer_listid')) {
                $this->container->get('settings')->setSetting('arcamailer_customer_listid','1');
                $this->container->get('settings')->getDomainSetting('arcamailer_customer_listid',true);//Needed to ensure the value on multidomain
            }
        }
        else {
            if(!$this->container->get('settings')->getDomainSetting('arcamailer_customer_id')) {
                $this->container->get('settings')->setSetting('arcamailer_customer_listid','0');
                $this->container->get('settings')->getDomainSetting('arcamailer_customer_listid',true);//Needed to ensure the value on multidomain
            }
        }
    }

    private function getLoadConfigAfterLoad(&$params = null)
    {
        if ($this->container->get('mailchimp.service')->isSetUp()) {
            define('MAIL_APP_FEATURE', 'off');
        }
        else {
            define('MAIL_APP_FEATURE', 'on');
        }
    }

    private function getConstantsAvoidMailAppDefines(&$params = null)
    {
        //Any hook that was fired or checked in the constants.inc.php will not have the container services set well. Avoid to use "$this->container->get" in methods fired by these hooks
        //DO nothing, just set this to avoid the MAIL_APP_FEATURE define to be able to define this constant on getLoadConfigAfterLoad
    }

    private function getNewsletterBeforeAddWidgetType(&$params = null)
    {
        if (!$params['settings'] = $this->container->get('mailchimp.service')->isSetUp()) {
            $params['linkForward'] = 'configuration/general-settings/#mailchimp-panel';//TODO: Will be need to navigate to this address when user chooses to use mailchimp when arcamailer and mailchimp options wasn't configurated. But it deppends to a basecode change in the arcamailer setup tab including the add of a new Hook
        } else {
            $params['classItem'] = 'addWidget';
        }
    }

    private function getNewsletterBeforeRenderJs(&$params = null)
    {
        if ($this->container->get('mailchimp.service')->isSetUp()) {
            echo '<script src="/bundles/mailchimpintegration/js/newsletter_mailchimp.js"></script>';
        } else {
            echo '<script src="' . DEFAULT_URL . '/' . SITEMGR_ALIAS . '/assets/js/newsletter.js"></script>';//TODO: Remove it after change the web/sitemgr/design/page-editor/custom-tabs/newsletter.php in eDirectory basecode to include it always, even if the hook has been fired
        }
    }

    private function getSitemgrHeaderAfterRenderMetatags(&$params = null)
    {
        echo '<link rel="stylesheet" href="/bundles/mailchimpintegration/css/newsletter_mailchimp.css"/>';//TODO: Remove when this entry was removed from basecode css (on web/sitemgr/assets/style/less/wysiwyg.less) : "&#tab-newsletter.active{"
    }


    private function getAccountCodeAfterSave(&$params = null)
    {
        if ($this->container->get('settings')->getDomainSetting('mailchimp_listid') && $_POST['newsletter_mailchimp']) {

            $this->container->get('mailchimp.service')->subscribeUser([
                'EMAIL' => $_POST['email'] ? $_POST['email'] : $_POST['username'],
                'FNAME' => $_POST['first_name'],
                'LNAME' => $_POST['last_name'],
            ]);

        }
    }

    private function getOrderListingBeforeRedirect(&$params = null)
    {
        if ($this->container->get('settings')->getDomainSetting('mailchimp_listid') && $_POST['newsletter_mailchimp']) {

            $this->container->get('mailchimp.service')->subscribeUser([
                'EMAIL' => $_POST['email'] ? $_POST['email'] : $_POST['username'],
                'FNAME' => $_POST['first_name'],
                'LNAME' => $_POST['last_name'],
            ]);

        }
    }

    private function getOrderArticleBeforeRedirect(&$params = null)
    {
        if ($this->container->get('settings')->getDomainSetting('mailchimp_listid') && $_POST['newsletter_mailchimp']) {

            $this->container->get('mailchimp.service')->subscribeUser([
                'EMAIL' => $_POST['email'] ? $_POST['email'] : $_POST['username'],
                'FNAME' => $_POST['first_name'],
                'LNAME' => $_POST['last_name'],
            ]);

        }
    }

    private function getOrderBannerBeforeRedirect(&$params = null)
    {
        if ($this->container->get('settings')->getDomainSetting('mailchimp_listid') && $_POST['newsletter_mailchimp']) {

            $this->container->get('mailchimp.service')->subscribeUser([
                'EMAIL' => $_POST['email'] ? $_POST['email'] : $_POST['username'],
                'FNAME' => $_POST['first_name'],
                'LNAME' => $_POST['last_name'],
            ]);

        }
    }

    private function getOrderClassifiedBeforeRedirect(&$params = null)
    {
        if ($this->container->get('settings')->getDomainSetting('mailchimp_listid') && $_POST['newsletter_mailchimp']) {

            $this->container->get('mailchimp.service')->subscribeUser([
                'EMAIL' => $_POST['email'] ? $_POST['email'] : $_POST['username'],
                'FNAME' => $_POST['first_name'],
                'LNAME' => $_POST['last_name'],
            ]);

        }
    }

    private function getOrderEventBeforeRedirect(&$params = null)
    {
        if ($this->container->get('settings')->getDomainSetting('mailchimp_listid') && $_POST['newsletter_mailchimp']) {

            $this->container->get('mailchimp.service')->subscribeUser([
                'EMAIL' => $_POST['email'] ? $_POST['email'] : $_POST['username'],
                'FNAME' => $_POST['first_name'],
                'LNAME' => $_POST['last_name'],
            ]);

        }
    }

    private function getFormSignupAfterRenderNewsletter(&$params = null)
    {
        if ($this->container->get('settings')->getDomainSetting('mailchimp_listid')) {
            echo $this->container->get('templating')->render('MailchimpIntegrationBundle::form_addaccount_newsletter.html.twig');

            $params['showNewsletter'] = false;
        }
    }
}
