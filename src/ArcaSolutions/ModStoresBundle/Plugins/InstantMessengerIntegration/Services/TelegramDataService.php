<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services;

use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\TelegramData;

class TelegramDataService extends InstantMessengerDataService
{
    /**
     * Helper function: Alphanumeric plus underscore string format validation.
     *
     * @param $str
     *
     * @return false|int
     */
    private function validate_alphanumeric_underscore($str)
    {
        return preg_match('/^[a-zA-Z0-9_]+$/', $str);
    }

    /**
     * @param array $instantMessengerIntegrationArrayFromHttpPost
     * @param TelegramData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInstantMessengerIntegrationArrayFromHttpPost(array $instantMessengerIntegrationArrayFromHttpPost, &$instantMessengerDataObject):bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof TelegramData){
            if (array_key_exists('nickname', $instantMessengerIntegrationArrayFromHttpPost)) {
                $instantMessengerDataObject->nickname = trim($instantMessengerIntegrationArrayFromHttpPost['nickname']);
                $returnValue = true;
            }
        }
        return $returnValue;
    }

    /**
     * @param mixed $instantMessengerIntegrationDataFromDecodedJson
     * @param TelegramData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInstantMessengerIntegrationDataFromDecodedJson($instantMessengerIntegrationDataFromDecodedJson, &$instantMessengerDataObject): bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof TelegramData && is_object($instantMessengerIntegrationDataFromDecodedJson)){
            if (property_exists($instantMessengerIntegrationDataFromDecodedJson, 'nickname')) {
                $instantMessengerDataObject->nickname = trim($instantMessengerIntegrationDataFromDecodedJson->nickname);
                $returnValue = true;
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
        if (array_key_exists('telegram', $httpPostArray)) {
            $telegram = $httpPostArray['telegram'];
            if (!empty($telegram) && is_array($telegram) && array_key_exists('nickname', $telegram)) {
                $telegram_nickname = trim($telegram['nickname']);
                if (!empty($telegram_nickname)) {
                    if (!$this->validate_alphanumeric_underscore($telegram_nickname) || strlen($telegram_nickname) < 5) {
                        $errorMessage = $this->translator->trans('Invalid Telegram nickname format: Use only letters, numbers, underscore character and use at least 5 (five) characters.',[],'messages','en');
                        $this->addFormErrorMessage($errorsMessageArray, $errorMessage);
                        $returnValue = false;
                    }
                }
                unset($telegram_nickname);
            }
            unset($telegram);
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
            if(($subject instanceof TelegramData) && ($comparisonSubject instanceof TelegramData)){
                /** @var TelegramData $subject */
                /** @var TelegramData $comparisonSubject */
                $returnValue = $returnValue && ($subject->nickname === $comparisonSubject->nickname);
            }
        }
        return $returnValue;
    }

    /**
     * @param mixed $injectedLegacyListingImObject
     * @param TelegramData $instantMessengerDataObject
     * @return bool
     */
    public function extractFromInjectedLegacyClassListingInstantMessengerObject($injectedLegacyListingImObject, &$instantMessengerDataObject): bool
    {
        $returnValue = false;
        if($instantMessengerDataObject instanceof TelegramData) {
            if (property_exists($injectedLegacyListingImObject, 'telegram') && !empty($injectedLegacyListingImObject->telegram)) {
                if (property_exists($injectedLegacyListingImObject->telegram, 'nickname_value')) {
                    $instantMessengerDataObject->nickname = trim($injectedLegacyListingImObject->telegram->nickname_value);
                    $returnValue = true;
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
        if ($instantMessengerData instanceof TelegramData) {
            $instantMessengerButtonData->type = $instantMessengerData::getInstantMessengerType();
            if (!empty($instantMessengerData->nickname)) {
                $instantMessengerButtonData->hRef = 'https://t.me/' . strtolower(trim($instantMessengerData->nickname));
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
        if ($instantMessengerData instanceof TelegramData) {
            $returnValue = $this->extractInstantMessengerButtonDataFromInstantMessengerData($instantMessengerData,$instantMessengerButtonData);
            if (!empty($instantMessengerData->nickname)) {
                $instantMessengerButtonData->value = $instantMessengerData->nickname;
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
            if ($instantMessengerData instanceof TelegramData) {
                $returnValue = empty($instantMessengerData->nickname);
            }
        }

        return $returnValue;
    }
}
