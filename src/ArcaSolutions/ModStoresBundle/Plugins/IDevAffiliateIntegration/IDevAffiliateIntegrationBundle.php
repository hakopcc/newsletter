<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\IDevAffiliateIntegration;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IDevAffiliateIntegrationBundle extends Bundle
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
                Hooks::Register('paymentfunct_after_paymentreceiveinvoice', function (&$params = null) {
                    return $this->getPaymentFunctAfterPaymentReceiveInvoice($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('loadconfig_after_load', function (&$params = null) {
                    return $this->getLoadConfigAfterLoad($params);
                });
                Hooks::Register('beforecontroller_after_onkernel', function (&$params = null) {
                    return $this->getBeforeControllerAfterOnKernel($params);
                });
                Hooks::Register('billingpay_before_render_page', function (&$params = null) {
                    return $this->getBillingPayBeforeRenderPage($params);
                });
                Hooks::Register('signuppayment_before_render_page', function (&$params = null) {
                    return $this->getSignupPaymentBeforeRenderPage($params);
                });
                Hooks::Register('signupprocesspayment_before_render_page', function (&$params = null) {
                    return $this->getSignupProcessPaymentBeforeRenderPage($params);
                });
                Hooks::Register('claimprocesspayment_before_render_page', function (&$params = null) {
                    return $this->getClaimProcessPaymentBeforeRenderPage($params);
                });
                Hooks::Register('billingprocesspayment_before_render_page', function (&$params = null) {
                    return $this->getBillingProcessPaymentBeforeRenderPage($params);
                });
                Hooks::Register('billinginvoice_before_render_page', function (&$params = null) {
                    return $this->getBillingInvoiceBeforeRenderPage($params);
                });
                Hooks::Register('signupinvoice_before_render_page', function (&$params = null) {
                    return $this->getSignupInvoiceBeforeRenderPage($params);
                });
                Hooks::Register('claiminvoice_before_render_page', function (&$params = null) {
                    return $this->getClaimInvoiceBeforeRenderPage($params);
                });

            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of IDevAffiliateIntegrationBundle.php', ['exception' => $e]);
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

            $settings->setSetting('generic_tracking_pixel', $_POST['generic_tracking_pixel']);
            $settings->setSetting('idev_session_duration', $_POST['idev_session_duration']);

            $params['success'] = true;
        }
    }

    private function getGeneralSettingsAfterRenderForm(&$params = null)
    {
        $settings = $this->container->get('settings');
        $translator = $this->container->get('translator');

        $arrayNameDD = [
            $translator->trans('Session'),
            $translator->trans('One day'),
            $translator->trans('One week'),
            $translator->trans('One month'),
        ];

        $arrayValueDD = [
            'cookie_0',
            'cookie_'.(1 * 24 * 3600),
            'cookie_'.(7 * 24 * 3600),
            'cookie_'.(30 * 24 * 3600),
        ];

        $durationDropdown = html_selectBox(
            'idev_session_duration',
            $arrayNameDD,
            $arrayValueDD,
            $settings->getDomainSetting('idev_session_duration', true),
            '',
            'class="form-control status-select"'
        );

        echo $this->container->get('templating')->render('IDevAffiliateIntegrationBundle::sitemgr-form-idev.html.twig',
            [
                'durationDropdown'       => $durationDropdown,
                'generic_tracking_pixel' => $settings->getDomainSetting('generic_tracking_pixel', true),
            ]);
    }

    private function getPaymentFunctAfterPaymentReceiveInvoice(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT affiliate FROM Invoice_Affiliate WHERE invoice_id = :invoice_id LIMIT 1');
        $statement->bindValue('invoice_id', $params['invoiceObj']->getNumber('id'));
        $statement->execute();

        if ($result = $statement->fetch()) {

            $this->container->get('idevaffiliate.service')->sendComission([
                'affiliate_id' => $result['affiliate'],
                'amount'       => $params['invoiceObj']->amount,
                'account_id'   => $params['invoiceObj']->account_id,
            ]);

            $statement = $connection->prepare('DELETE FROM Invoice_Affiliate WHERE invoice_id = :invoice_id');
            $statement->bindValue('invoice_id', $params['invoiceObj']->getNumber('id'));
            $statement->execute();

        }
    }

    private function getLoadConfigAfterLoad(&$params = null)
    {
        if (isset($_GET['affiliate']) || isset($_GET['idev_id'])) {

            $idev_affiliate = '';
            isset($_GET['affiliate']) and $idev_affiliate = $_GET['affiliate'];
            isset($_GET['idev_id']) and $idev_affiliate = $_GET['idev_id'];

            $duration = $this->container->get('settings')->getDomainSetting('idev_session_duration');
            $duration = str_replace('cookie_', '', $duration);
            $duration = $duration ? time() + $duration : 0;

            $response = new Response();
            $response->headers->setCookie(new Cookie('idev_affiliate', $idev_affiliate, $duration));
            $response->prepare(Request::createFromGlobals());
            $response->sendHeaders();

        }
    }

    private function getBeforeControllerAfterOnKernel(&$params = null)
    {
        if (isset($_GET['affiliate']) || isset($_GET['idev_id'])) {

            $idev_affiliate = '';
            isset($_GET['affiliate']) and $idev_affiliate = $_GET['affiliate'];
            isset($_GET['idev_id']) and $idev_affiliate = $_GET['idev_id'];

            $duration = $this->container->get('settings')->getDomainSetting('idev_session_duration');
            $duration = str_replace('cookie_', '', $duration);
            $duration = $duration ? time() + $duration : 0;

            $response = new Response();
            $response->headers->setCookie(new Cookie('idev_affiliate', $idev_affiliate, $duration));
            $response->prepare(Request::createFromGlobals());
            $response->sendHeaders();

        }
    }

    public function getBillingPayBeforeRenderPage(&$params = null)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $response = new Response();
            $response->headers->setCookie(new Cookie('idev_sale_amount', $params['bill_info']['amount']));
            $response->prepare(Request::createFromGlobals());
            $response->sendHeaders();

        }
    }

    public function getSignupPaymentBeforeRenderPage(&$params = null)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $response = new Response();
            $response->headers->setCookie(new Cookie('idev_sale_amount', $params['bill_info']['amount']));
            $response->prepare(Request::createFromGlobals());
            $response->sendHeaders();

        }
    }

    private function getSignupProcessPaymentBeforeRenderPage(&$params = null)
    {
        if ($params['payment_success'] == 'y' && $this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $amount = $params['payment_amount'] ? $params['payment_amount'] : $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_sale_amount');
            $this->container->get('idevaffiliate.service')->sendComission([
                'affiliate_id' => $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_affiliate'),
                'amount'       => $amount,
            ]);

        }

        $response = new Response();
        $response->headers->clearCookie('idev_sale_amount');
        $response->prepare(Request::createFromGlobals());
        $response->sendHeaders();
    }

    private function getClaimProcessPaymentBeforeRenderPage(&$params = null)
    {
        if ($params['payment_success'] == 'y' && $this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $amount = $params['payment_amount'] ? $params['payment_amount'] : $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_sale_amount');
            $this->container->get('idevaffiliate.service')->sendComission([
                'affiliate_id' => $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_affiliate'),
                'amount'       => $amount,
            ]);

        }

        $response = new Response();
        $response->headers->clearCookie('idev_sale_amount');
        $response->prepare(Request::createFromGlobals());
        $response->sendHeaders();
    }

    private function getBillingProcessPaymentBeforeRenderPage(&$params = null)
    {
        if ($params['payment_success'] == 'y' && $this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $amount = $params['payment_amount'] ? $params['payment_amount'] : $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_sale_amount');
            $this->container->get('idevaffiliate.service')->sendComission([
                'affiliate_id' => $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_affiliate'),
                'amount'       => $amount,
            ]);

        }

        $response = new Response();
        $response->headers->clearCookie('idev_sale_amount');
        $response->prepare(Request::createFromGlobals());
        $response->sendHeaders();
    }

    private function getBillingInvoiceBeforeRenderPage(&$params = null)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $em = $this->container->get('doctrine')->getManager();
            $connection = $em->getConnection();

            $statement = $connection->prepare('INSERT INTO Invoice_Affiliate (`invoice_id`, `affiliate`) VALUES (:invoice_id, :affiliate)');
            $statement->bindValue('invoice_id', $params['invoiceObj']->getNumber('id'));
            $statement->bindValue('affiliate',
                $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_affiliate'));
            $statement->execute();

        }
    }

    private function getSignupInvoiceBeforeRenderPage(&$params = null)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $em = $this->container->get('doctrine')->getManager();
            $connection = $em->getConnection();

            $statement = $connection->prepare('INSERT INTO Invoice_Affiliate (`invoice_id`, `affiliate`) VALUES (:invoice_id, :affiliate)');
            $statement->bindValue('invoice_id', $params['invoiceObj']->getNumber('id'));
            $statement->bindValue('affiliate',
                $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_affiliate'));
            $statement->execute();

        }
    }

    private function getClaimInvoiceBeforeRenderPage(&$params = null)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->cookies->has('idev_affiliate')) {

            $em = $this->container->get('doctrine')->getManager();
            $connection = $em->getConnection();

            $statement = $connection->prepare('INSERT INTO Invoice_Affiliate (`invoice_id`, `affiliate`) VALUES (:invoice_id, :affiliate)');
            $statement->bindValue('invoice_id', $params['invoiceObj']->getNumber('id'));
            $statement->bindValue('affiliate',
                $this->container->get('request_stack')->getCurrentRequest()->cookies->get('idev_affiliate'));
            $statement->execute();

        }
    }
}
