<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\IDevAffiliateIntegration\Services;

use DOMDocument;
use DOMXPath;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class IDevAffiliateService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sendComission($params)
    {
        if ($this->container->get('settings')->getDomainSetting('generic_tracking_pixel') && !empty($params['amount']) && !empty($params['affiliate_id'])) {

            $request = $this->container->get('settings')->getDomainSetting('generic_tracking_pixel');

            $xpath = new DOMXPath(@DOMDocument::loadHTML($request));
            $request = $xpath->evaluate('string(//img/@src)');

            $account = $params['account_id'] ? $params['account_id'] : $_SESSION['SESS_ACCOUNT_ID'];

            $params['amount'] = number_format($params['amount'], 2);
            $params['ordernum'] = $account.'_'.date('mdY').'_'.number_format($params['amount'], 2, '', '');

            $request = str_replace('idev_saleamt=XXX', 'idev_saleamt='.$params['amount'], $request);
            $request = str_replace(['idev_ordernum=XXX', 'ip_address=XXX'],
                array('idev_ordernum='.$params['ordernum'], 'ip_address='.$params['ip']), $request);

            if (!empty($params['affiliate_id'])) {
                if (strpos($request, 'affiliate_id') === false) {
                    $request .= '&affiliate_id='.$params['affiliate_id'];
                } else {
                    $request = str_replace('affiliate_id=XXX', 'affiliate_id='.$params['affiliate_id'], $request);
                }
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);

        }
    }
}
