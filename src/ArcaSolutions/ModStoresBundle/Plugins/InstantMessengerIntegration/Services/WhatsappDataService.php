<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services;

use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\WhatsappData;

class WhatsappDataService extends InstantMessengerDataService
{
    /**
     * Helper function: Phone number country code existence checker.
     *
     * @param $str
     *
     * @return false|int
     */
    private function check_countrycode_existance($str)
    {
        return preg_match('/^\+[0-9]{1,3}.*$/', $str);
    }

    /**
     * Helper function: Phone number format validation.
     *
     * @param $str
     *
     * @return false|int
     */
    private function validate_phone_number($str)
    {
        return preg_match('/^([+][0-9]{1,3})?\s*[(]?[\s0-9]{1,4}[)]?[\-\s.0-9]+$/', $str);
    }

    /**
     * @param array $instantMessengerIntegrationArrayFromHttpPost
     * @param WhatsappData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInstantMessengerIntegrationArrayFromHttpPost(array $instantMessengerIntegrationArrayFromHttpPost, &$instantMessengerDataObject):bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof WhatsappData){
            if (array_key_exists('country_code', $instantMessengerIntegrationArrayFromHttpPost)) {
                $instantMessengerDataObject->country_code = trim($instantMessengerIntegrationArrayFromHttpPost['country_code']);
                $returnValue = true;
            }
            if (array_key_exists('number', $instantMessengerIntegrationArrayFromHttpPost)) {
                $instantMessengerDataObject->number = trim($instantMessengerIntegrationArrayFromHttpPost['number']);
            } else if ($returnValue) {
                $returnValue = false;
            }
        }
        return $returnValue;
    }

    /**
     * @param mixed $instantMessengerIntegrationDataFromDecodedJson
     * @param WhatsappData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInstantMessengerIntegrationDataFromDecodedJson($instantMessengerIntegrationDataFromDecodedJson, &$instantMessengerDataObject): bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof WhatsappData && is_object($instantMessengerIntegrationDataFromDecodedJson)) {
            if (property_exists($instantMessengerIntegrationDataFromDecodedJson, 'country_code')) {
                $instantMessengerDataObject->country_code = trim($instantMessengerIntegrationDataFromDecodedJson->country_code);
                $returnValue = true;
            }
            if (property_exists($instantMessengerIntegrationDataFromDecodedJson, 'number')) {
                $instantMessengerDataObject->number = trim($instantMessengerIntegrationDataFromDecodedJson->number);
            } else if ($returnValue) {
                $returnValue = false;
            }
        }
        return $returnValue;
    }

    /**
     * @param array $httpPostArray
     * @param array $errorsMessageArray
     * @return bool
     */
    public function validateDataFromHttpPostArray(array $httpPostArray, &$errorsMessageArray): bool
    {
        $returnValue = true;
        if (array_key_exists('whatsapp', $httpPostArray)) {
            $whatsapp = $httpPostArray['whatsapp'];
            if (!empty($whatsapp) && is_array($whatsapp) && array_key_exists('country_code', $whatsapp) && array_key_exists('number', $whatsapp)) {
                $whatsapp_country_code = $whatsapp['country_code'];
                $whatsapp_number = $whatsapp['number'];
                if (!empty($whatsapp_country_code) || !empty($whatsapp_number)) {
                    if (empty($whatsapp_country_code)) {
                        $errorMessage = $this->translator->trans('To define a WhatsApp number is mandatory select a country code and provide a phone number (preceded by the area code, when exists). Please select a country code.',[],'messages','en');
                        $this->addFormErrorMessage($errorsMessageArray, $errorMessage);
                        $returnValue = false;
                    } elseif (empty($whatsapp_number)) {
                        $errorMessage = $this->translator->trans('To define a WhatsApp number is mandatory select a country code and provide a phone number (preceded by the area code, when exists). Please provide the phone number.',[],'messages','en');
                        $this->addFormErrorMessage($errorsMessageArray, $errorMessage);
                        $returnValue = false;
                    } else {
                        if (!$this->validate_phone_number($whatsapp_number)) {
                            $errorMessage = $this->translator->trans('Invalid phone format in WhatsApp number.',[],'messages','en');
                            $this->addFormErrorMessage($errorsMessageArray, $errorMessage);
                            $returnValue = false;
                        } elseif ($this->check_countrycode_existance($whatsapp_number)) {
                            $errorMessage = $this->translator->trans('Do not include the country code in WhatsApp number. Use the select field to define it.',[],'messages','en');
                            $this->addFormErrorMessage($errorsMessageArray, $errorMessage);
                            $returnValue = false;
                        }
                    }
                }
                unset($whatsapp_country_code,
                    $whatsapp_number);
            }
            unset($whatsapp);
        }
        return $returnValue;
    }

    /**
     * @param InstantMessengerData $subject
     * @param InstantMessengerData $comparisonSubject
     * @return bool
     */
    public function isDataEqual(InstantMessengerData $subject, InstantMessengerData $comparisonSubject): bool
    {
        $returnValue = parent::isDataEqual($subject,$comparisonSubject);
        if($returnValue)
        {
            if(($subject instanceof WhatsappData) && ($comparisonSubject instanceof WhatsappData)){
                /** @var WhatsappData $subject */
                /** @var WhatsappData $comparisonSubject */
                $returnValue = $returnValue && ($subject->country_code === $comparisonSubject->country_code);
                $returnValue = $returnValue && ($subject->number === $comparisonSubject->number);
            }
        }
        return $returnValue;
    }

    /**
     * @param mixed $injectedLegacyListingImObject
     * @param WhatsappData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInjectedLegacyClassListingInstantMessengerObject($injectedLegacyListingImObject, &$instantMessengerDataObject): bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof WhatsappData){
            if (property_exists($injectedLegacyListingImObject, 'whatsapp') && !empty($injectedLegacyListingImObject->whatsapp)) {
                if (property_exists($injectedLegacyListingImObject->whatsapp, 'country_code_value')) {
                    $instantMessengerDataObject->country_code = trim($injectedLegacyListingImObject->whatsapp->country_code_value);
                    $returnValue = true;
                }
                if (property_exists($injectedLegacyListingImObject->whatsapp, 'number_value')) {
                    $instantMessengerDataObject->number = trim($injectedLegacyListingImObject->whatsapp->number_value);
                } else if ($returnValue) {
                    $returnValue = false;
                }
            }
        }
        return $returnValue;
    }

    /**
     * @param InstantMessengerData $instantMessengerData
     * @param InstantMessengerButtonData $instantMessengerButtonData
     * @return bool
     */
    public function extractInstantMessengerButtonDataFromInstantMessengerData(InstantMessengerData $instantMessengerData, InstantMessengerButtonData $instantMessengerButtonData): bool
    {
        $returnValue = false;
        if ($instantMessengerData instanceof WhatsappData) {
            $instantMessengerButtonData->type = $instantMessengerData::getInstantMessengerType();
            if (!empty($instantMessengerData->country_code) && !empty($instantMessengerData->number)) {
                $instantMessengerButtonData->hRef = 'https://wa.me/' . $instantMessengerData->country_code . preg_replace('/[^0-9]/', '', $instantMessengerData->number);
                $returnValue = true;
            }
            $returnValue = $this->extractIconSvgRawContentFromInstantMessengerData($instantMessengerData, $instantMessengerButtonData) && $returnValue;
        }
        return $returnValue;
    }

    /**
     * @param InstantMessengerData $instantMessengerData
     * @param InstantMessengerLinkButtonData $instantMessengerButtonData
     * @return bool
     */
    public function extractInstantMessengerLinkButtonDataFromInstantMessengerData(InstantMessengerData $instantMessengerData, InstantMessengerLinkButtonData $instantMessengerButtonData): bool
    {
        $returnValue = false;
        if ($instantMessengerData instanceof WhatsappData) {
            $returnValue = $this->extractInstantMessengerButtonDataFromInstantMessengerData($instantMessengerData, $instantMessengerButtonData);
            $includeCountryCodeOnWhatsAppValue = false; //By definition the inclusion of the country code in the whatsapp value on the screen will be not included. If that changes or if will need to check this option from elsewhere, use this variable
            if (!empty($instantMessengerData->country_code) && !empty($instantMessengerData->number)) {
                $instantMessengerButtonData->value = ($includeCountryCodeOnWhatsAppValue ? '+'.$instantMessengerData->country_code.' ' : '').$instantMessengerData->number;
            } else {
                $returnValue = false;
            }
        }
        return $returnValue;
    }

    /**
     * @param InstantMessengerData $instantMessengerData
     * @return bool
     */
    public function isDataEmpty(InstantMessengerData $instantMessengerData): bool
    {
        $returnValue = $instantMessengerData===null;

        if(!$returnValue) {
            if ($instantMessengerData instanceof WhatsappData) {
                $returnValue = empty($instantMessengerData->country_code) &&  empty($instantMessengerData->number);
            }
        }

        return $returnValue;
    }
}
