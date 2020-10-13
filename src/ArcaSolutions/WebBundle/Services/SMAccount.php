<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\CoreBundle\Entity\Setting;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SMAccount
{
    /**
     * @var ContainerInterface
     */
    private $container;
    const SITEMGR_PERMISSION_SECTION = 9;
    const SITEMGR_PERMISSION_ID = [
        'SITEMGR_PERMISSION_SITES'       => 1,
        'SITEMGR_PERMISSION_ACCOUNTS'    => 2,
        'SITEMGR_PERMISSION_CONTENT'     => 4,
        'SITEMGR_PERMISSION_ACTIVITY'    => 8,
        'SITEMGR_PERMISSION_PROMOTE'     => 16,
        'SITEMGR_PERMISSION_DESIGN'      => 32,
        'SITEMGR_PERMISSION_CONFIG'      => 64,
        'SITEMGR_PERMISSION_MOBILE'      => 128,
        'SITEMGR_PERMISSION_SUPERADMIN'  => 256,
    ];
    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function sessionSmId(){
        return $this->container->get('session')->get('SESS_SM_ID');
    }

    /**
     * @return mixed
     */
    public function sessionSmPerm(){
        return $this->container->get('session')->get('SESS_SM_PERM');
    }

    public function permission_hasSMPermSection($sectionid = false)
    {
        if ($sectionid) {
            if ($this->sessionSmId()) {
                if ($this::SITEMGR_PERMISSION_SECTION > 0) {
                    for ($i = 0; $i < $this::SITEMGR_PERMISSION_SECTION; $i++) {
                        $sess_sm_perm = decbin($this->sessionSmPerm());
                        while (string_strlen($sess_sm_perm) < $this::SITEMGR_PERMISSION_SECTION) {
                            $sess_sm_perm = "0" . $sess_sm_perm;
                        }
                        $sectionid = decbin($sectionid);
                        while (string_strlen($sectionid) < $this::SITEMGR_PERMISSION_SECTION) {
                            $sectionid = "0" . $sectionid;
                        }
                        if (($sess_sm_perm & $sectionid) == $sectionid) {
                            return true;
                        }
                    }
                }
            } elseif ($sectionid != $this::SITEMGR_PERMISSION_ID["SITEMGR_PERMISSION_SUPERADMIN"]) {
                return true;
            }
        }
        return false;

    }

}
