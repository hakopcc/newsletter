<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services;


use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\FacebookMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonData;

class FacebookMessengerDataService extends InstantMessengerDataService
{
    /**
     * Helper function: Alphanumeric plus dot string format validation.
     *
     * @param $str
     *
     * @return false|int
     */
    private function validate_alphanumeric_dot($str)
    {
        return preg_match('/^[a-zA-Z0-9.]+$/', $str);
    }

    /**
     * @param array $instantMessengerIntegrationArrayFromHttpPost
     * @param FacebookMessengerData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInstantMessengerIntegrationArrayFromHttpPost(array $instantMessengerIntegrationArrayFromHttpPost, &$instantMessengerDataObject):bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof FacebookMessengerData){
            if (array_key_exists('user_id', $instantMessengerIntegrationArrayFromHttpPost)) {
                $instantMessengerDataObject->user_id = trim($instantMessengerIntegrationArrayFromHttpPost['user_id']);
                $returnValue = true;
            }
            if (array_key_exists('caption', $instantMessengerIntegrationArrayFromHttpPost)) {
                $instantMessengerDataObject->caption = trim($instantMessengerIntegrationArrayFromHttpPost['caption']);
            } else {
                $returnValue = false;
            }
        }
        return $returnValue;
    }

    /**
     * @param mixed $instantMessengerIntegrationDataFromDecodedJson
     * @param FacebookMessengerData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInstantMessengerIntegrationDataFromDecodedJson($instantMessengerIntegrationDataFromDecodedJson, &$instantMessengerDataObject): bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof FacebookMessengerData && is_object($instantMessengerIntegrationDataFromDecodedJson)) {
            if (property_exists($instantMessengerIntegrationDataFromDecodedJson, 'user_id')) {
                $instantMessengerDataObject->user_id = trim($instantMessengerIntegrationDataFromDecodedJson->user_id);
                $returnValue = true;
            }
            if (property_exists($instantMessengerIntegrationDataFromDecodedJson, 'caption')) {
                $instantMessengerDataObject->caption = trim($instantMessengerIntegrationDataFromDecodedJson->caption);
            } else {
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
        if (array_key_exists('messenger', $httpPostArray)) {
            $messenger = $httpPostArray['messenger'];
            if (!empty($messenger) && is_array($messenger) && array_key_exists('user_id', $messenger)) {
                $messenger_user_id = trim($messenger['user_id']);
                if (!empty($messenger_user_id)) {
                    if (!$this->validate_alphanumeric_dot($messenger_user_id) || strlen($messenger_user_id) < 5) {
                        $errorMessage = $this->translator->trans('Invalid Messenger User ID format: Use only letters, numbers, dot character and use at least 5 (five) characters.',[],'messages','en');
                        $this->addFormErrorMessage($errorsMessageArray, $errorMessage);
                        $returnValue = false;
                    }
                }
                unset($messenger_user_id);
            }
            unset($messenger);
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
            if(($subject instanceof FacebookMessengerData) && ($comparisonSubject instanceof FacebookMessengerData)){
                /** @var FacebookMessengerData $subject */
                /** @var FacebookMessengerData $comparisonSubject */
                $returnValue = $returnValue && ($subject->user_id === $comparisonSubject->user_id) && ($subject->caption === $comparisonSubject->caption);
            }
        }
        return $returnValue;
    }

    /**
     * @param mixed $injectedLegacyListingImObject
     * @param FacebookMessengerData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInjectedLegacyClassListingInstantMessengerObject($injectedLegacyListingImObject, &$instantMessengerDataObject): bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof FacebookMessengerData) {
            if (property_exists($injectedLegacyListingImObject, 'messenger') && !empty($injectedLegacyListingImObject->messenger)) {
                if (property_exists($injectedLegacyListingImObject->messenger, 'user_id_value')) {
                    $instantMessengerDataObject->user_id = trim($injectedLegacyListingImObject->messenger->user_id_value);
                    $returnValue = true;
                }
                if (property_exists($injectedLegacyListingImObject->messenger, 'caption_value')) {
                    $instantMessengerDataObject->caption = trim($injectedLegacyListingImObject->messenger->caption_value);
                } else {
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
        if ($instantMessengerData instanceof FacebookMessengerData) {
            $instantMessengerButtonData->type = $instantMessengerData::getInstantMessengerType();
            if (!empty($instantMessengerData->user_id)) {
                $instantMessengerButtonData->hRef = 'https://m.me/' . strtolower(trim($instantMessengerData->user_id));
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
        if ($instantMessengerData instanceof FacebookMessengerData) {
            $returnValue = $this->extractInstantMessengerButtonDataFromInstantMessengerData($instantMessengerData,$instantMessengerButtonData);
            if (!empty($instantMessengerData->user_id)) {
                if (!empty($instantMessengerData->caption)) {
                    $instantMessengerButtonData->value = $instantMessengerData->caption;
                } else {
                    $instantMessengerButtonData->value = $instantMessengerData->user_id;
                }
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
            if ($instantMessengerData instanceof FacebookMessengerData) {
                $returnValue = empty($instantMessengerData->user_id) && empty($instantMessengerData->caption);
            }
        }

        return $returnValue;
    }
}
