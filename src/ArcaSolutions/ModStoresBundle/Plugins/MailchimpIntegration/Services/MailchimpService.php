<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MailchimpIntegration\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MailchimpService
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function subscribeUser($fields)
    {
        $this->container->get('mailchimpapi.service')->setKey($this->container->get('settings')->getDomainSetting('mailchimp_apikey'));
        
        $result = $this->container->get('mailchimpapi.service')->get('lists/'.$this->container->get('settings')->getDomainSetting('mailchimp_listid').'/members/'.md5($fields['EMAIL']));
        if (!isset($result['unique_email_id'])) {
            
            $mergeFields['FNAME'] = $fields['FNAME'];
            $mergeFields['LNAME'] = isset($fields['LNAME']) ? $fields['LNAME'] : '';
            
            $fields['FNAME'] = trim($fields['FNAME']);
            if (preg_match('/\s+/', $fields['FNAME'])) {
                $names = explode(' ', $fields['FNAME']);
                $mergeFields['LNAME'] = array_pop($names);
                $mergeFields['FNAME'] = implode(' ', $names);
            }
            
            return $this->container->get('mailchimpapi.service')->post('lists/'.$this->container->get('settings')->getDomainSetting('mailchimp_listid').'/members',
                [
                    'status'        => 'subscribed',
                    'email_address' => $fields['EMAIL'],
                    'merge_fields'  => $mergeFields,
                ]);
        }
        
        return true;
    }
    
    public function isSetUp()
    {
        if ($this->getLists()) {
            if(!empty($this->container->get('settings')->getDomainSetting('mailchimp_listid'))) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getLists()
    {
        $this->container->get('mailchimpapi.service')->setKey($this->container->get('settings')->getDomainSetting('mailchimp_apikey'));
        
        return $this->container->get('mailchimpapi.service')->get('lists');
    }
}
