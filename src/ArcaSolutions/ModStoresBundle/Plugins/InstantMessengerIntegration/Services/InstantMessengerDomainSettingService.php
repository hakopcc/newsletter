<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services;


use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerDomainSetting;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Exceptions\InstantMessengerServiceException;
use Exception;

class InstantMessengerDomainSettingService
{
    /**
     * @param array $httpPostArray
     * @param InstantMessengerDomainSetting $instantMessengerDomainSetting
     * @return bool
     */
    public function extractDomainSettingFromHttpPostArray(array $httpPostArray, &$instantMessengerDomainSetting):bool{
        $returnValue = false;
        if (($instantMessengerDomainSetting instanceof InstantMessengerDomainSetting) && !empty($httpPostArray) && is_array($httpPostArray) && array_key_exists('plugin', $httpPostArray)){
            $plugin = $httpPostArray['plugin'];
            if (!empty($plugin) && is_array($plugin) && array_key_exists('instant_messenger_integration', $plugin)) {
                $instant_messenger_integration = $plugin['instant_messenger_integration'];
                if (!empty($instant_messenger_integration) && is_array($instant_messenger_integration)) {
                    if (array_key_exists('display_floating_button', $instant_messenger_integration)) {
                        $instantMessengerDomainSetting->displayFloatingButtonOption = ($instant_messenger_integration['display_floating_button']==='on') ? 'on' : 'off';
                        $returnValue = true;
                    } else {
                        $instantMessengerDomainSetting->displayFloatingButtonOption = 'off';
                    }
                    if (array_key_exists('floating_button_position', $instant_messenger_integration)) {
                        $instantMessengerDomainSetting->floatingButtonPosition = $instant_messenger_integration['floating_button_position'];
                    } elseif ($returnValue) {
                        $returnValue = false;
                    }
                    if (array_key_exists('floating_button_type', $instant_messenger_integration)) {
                        $instantMessengerDomainSetting->floatingButtonInstantMessengerType = $instant_messenger_integration['floating_button_type'];
                    } elseif ($returnValue) {
                        $returnValue = false;
                    }
                }
                unset($instant_messenger_integration);
            }
            unset($plugin);
        }
        return $returnValue;
    }

    /**
     * @param string $encodedJsonString
     * @param InstantMessengerDomainSetting $instantMessengerDomainSetting
     * @return bool
     * @throws InstantMessengerServiceException
     */
    public function extractDomainSettingFromEncodedJson(string $encodedJsonString, &$instantMessengerDomainSetting):bool{
        $returnValue = false;
        if (!empty($encodedJsonString)) {
            $decodedJsonImDomainSetting = json_decode($encodedJsonString, false);
            if (JSON_ERROR_NONE === json_last_error()) {
                if (!empty($decodedJsonImDomainSetting)) {
                    if (property_exists($decodedJsonImDomainSetting, 'displayFloatingButtonOption')) {
                        $instantMessengerDomainSetting->displayFloatingButtonOption = ($decodedJsonImDomainSetting->displayFloatingButtonOption === 'on') ? 'on' : 'off';
                        $returnValue = true;
                    } else {
                        $instantMessengerDomainSetting->displayFloatingButtonOption = 'off';
                    }
                    if (property_exists($decodedJsonImDomainSetting, 'floatingButtonPosition')) {
                        $instantMessengerDomainSetting->floatingButtonPosition = $decodedJsonImDomainSetting->floatingButtonPosition;
                    } elseif ($returnValue) {
                        $returnValue = false;
                    }
                    if (property_exists($decodedJsonImDomainSetting, 'floatingButtonInstantMessengerType')) {
                        $instantMessengerDomainSetting->floatingButtonInstantMessengerType = $decodedJsonImDomainSetting->floatingButtonInstantMessengerType;
                    } elseif ($returnValue) {
                        $returnValue = false;
                    }
                }
            } else {
                $e = new Exception(json_last_error_msg(), json_last_error());
                throw new InstantMessengerServiceException('Unexpected json error on extractDomainSettingFromEncodedJson method of InstantMessengerDomainSettingService.php', $e);
            }
            unset($decodedJsonInstantMessengerSiteManagerData);
        }
        return $returnValue;
    }

    /**
     * @param InstantMessengerDomainSetting $instantMessengerDomainSettingSource
     * @param InstantMessengerDomainSetting $instantMessengerDomainSettingComparison
     * @return bool
     */
    public function isInstantMessengerDomainSettingsEqual(InstantMessengerDomainSetting $instantMessengerDomainSettingSource, InstantMessengerDomainSetting $instantMessengerDomainSettingComparison):bool
    {
        $returnValue = ($instantMessengerDomainSettingSource->displayFloatingButtonOption === $instantMessengerDomainSettingComparison->displayFloatingButtonOption);
        $returnValue = $returnValue && ($instantMessengerDomainSettingSource->floatingButtonPosition === $instantMessengerDomainSettingComparison->floatingButtonPosition);
        $returnValue = $returnValue && ($instantMessengerDomainSettingSource->floatingButtonInstantMessengerType === $instantMessengerDomainSettingComparison->floatingButtonInstantMessengerType);
        return $returnValue;
    }

    /**
     * @param InstantMessengerDomainSetting $instantMessengerDomainSetting
     * @return bool
     */
    public function isDomainSettingEmpty(InstantMessengerDomainSetting $instantMessengerDomainSetting):bool
    {
        return $instantMessengerDomainSetting===null||(empty($instantMessengerDomainSetting->floatingButtonInstantMessengerType)&&empty($instantMessengerDomainSetting->floatingButtonPosition));
    }
}
