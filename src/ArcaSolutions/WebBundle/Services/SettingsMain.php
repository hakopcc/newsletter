<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\CoreBundle\Entity\Setting;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingsMain
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSettingsConsent(){
        $repository = $this->container->get('doctrine')->getRepository('WebBundle:Setting');
        $consent = $repository->findOneBy(array('name' => 'userconsent_status'));
        if(!empty($consent)){
            return $consent;
        }
        $consent = new Setting();
        $consent->setName('userconsent_status');
        $consent->setValue('off');
        return  $consent;
    }
}
