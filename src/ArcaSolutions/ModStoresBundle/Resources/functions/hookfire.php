<?php

use ArcaSolutions\ModStoresBundle\Kernel\Hooks;

/**
 * Fire Hooks::Fire globally through autoload
 *
 * @param string $hookName
 * @param array $params
 * @param bool $returnResult
 * @return boolean
 */
if (!function_exists('HookFire')) {

    function HookFire($hookName = null, $params = null, $returnResult = null)
    {
        if($returnResult===null){
            return Hooks::Fire($hookName, $params);
        } else {
            return Hooks::Fire($hookName, $params, $returnResult);
        }
    }
}

