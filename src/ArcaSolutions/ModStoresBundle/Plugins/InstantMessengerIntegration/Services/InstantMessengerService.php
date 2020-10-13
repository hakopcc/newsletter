<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services;

use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerDataArray;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\FacebookMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerDataClassesArray;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerDomainSetting;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerFloatingButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonDataArray;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\TelegramData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\WhatsappData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\ListingInstantMessenger;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Exceptions\InstantMessengerServiceException;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Repository\ListingInstantMessengerRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Exception;
use Listing as LegacyListing;

class InstantMessengerService
{
    /** @var FacebookMessengerDataService $_facebookMessengerDataService */
    private $_facebookMessengerDataService;

    /** @var WhatsappDataService $_whatsappDataService */
    private $_whatsappDataService;

    /** @var TelegramDataService $_telegramDataService */
    private $_telegramDataService;

    /** @var InstantMessengerDomainSettingService $_instantMessengerDomainSettingService */
    private $_instantMessengerDomainSettingService;

    /** @var Registry $_doctrine */
    private $_doctrine;

    /** @var ListingInstantMessengerRepository|ObjectRepository  */
    private $_listingInstantMessengerRepository;

    /**
     * InstantMessengerService constructor.
     * @param FacebookMessengerDataService $facebookMessengerDataService
     * @param WhatsappDataService $whatsappDataService
     * @param TelegramDataService $telegramDataService
     * @param InstantMessengerDomainSettingService $instantMessengerDomainSettingService
     * @param Registry $doctrine
     */
    public function __construct(FacebookMessengerDataService $facebookMessengerDataService, WhatsappDataService $whatsappDataService, TelegramDataService $telegramDataService, InstantMessengerDomainSettingService $instantMessengerDomainSettingService, Registry $doctrine)
    {
        $this->_facebookMessengerDataService = $facebookMessengerDataService;
        $this->_whatsappDataService = $whatsappDataService;
        $this->_telegramDataService = $telegramDataService;
        $this->_instantMessengerDomainSettingService = $instantMessengerDomainSettingService;
        $this->_doctrine = $doctrine;
        if($doctrine !== null) {
            $this->_listingInstantMessengerRepository = $doctrine->getRepository('InstantMessengerIntegrationBundle:ListingInstantMessenger');
        }
    }

    public function setLang($lang){
        $this->_facebookMessengerDataService->setLang($lang);
        $this->_whatsappDataService->setLang($lang);
        $this->_telegramDataService->setLang($lang);
    }

    /**
     * @param InstantMessengerDataClassesArray $instantMessengerDataClassesArray
     * @return object
     */
    public function getFloatingButtonInstantMessengerTypeData(InstantMessengerDataClassesArray $instantMessengerDataClassesArray)
    {
        $floatingButtonInstantMessengerTypeData = null;
        $this->executeActionOverInstantMessengerDataClassesArrayItemsTypes($instantMessengerDataClassesArray,
            static function($instantMessengerDataType,$instantMessengerClass,&$actionFunctionExtraParameter){
                if($actionFunctionExtraParameter===null){
                    $actionFunctionExtraParameter = (object)[
                        'options' => array(),
                        'defaultOption' => ''
                    ];
                }
                switch ($instantMessengerDataType) {
                    case WhatsappData::getInstantMessengerType():
                        $actionFunctionExtraParameter->options[$instantMessengerDataType] = 'WhatsApp';
                        break;
                    case FacebookMessengerData::getInstantMessengerType():
                        $actionFunctionExtraParameter->options[$instantMessengerDataType] = 'Facebook Messenger';
                        break;
                    case TelegramData::getInstantMessengerType():
                        $actionFunctionExtraParameter->options[$instantMessengerDataType] = 'Telegram';
                        break;
                }
                $returnValue = !empty($actionFunctionExtraParameter->options[$instantMessengerDataType]);
                if($returnValue){
                    if(empty($actionFunctionExtraParameter->defaultOption)){
                        $actionFunctionExtraParameter->defaultOption = $instantMessengerDataType;
                    }
                }
                return $returnValue;
            },$floatingButtonInstantMessengerTypeData);

        if($floatingButtonInstantMessengerTypeData===null){
            $floatingButtonInstantMessengerTypeData = (object)[
                'options' => array(),
                'defaultOption' => ''
            ];
        }
        return $floatingButtonInstantMessengerTypeData;
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @param callable $actionFunction
     * @param mixed|null $actionFunctionExtraParameter
     * @param bool $responseCalculatedUsingOr
     * @return bool
     */
    protected function executeActionOverInstantMessengerDataObjectAndService(&$instantMessengerDataArray, $actionFunction, &$actionFunctionExtraParameter = null, $responseCalculatedUsingOr = false): bool {
        $returnValue = null;
        if(is_callable($actionFunction)) {
            foreach ($instantMessengerDataArray as &$instantMessengerDataObject) {
                if ($instantMessengerDataObject !== null) {
                    /** @var InstantMessengerDataService $instantMessengerDataService */
                    $instantMessengerDataService = null;
                    $instantMessengerDataType = $instantMessengerDataObject->getInstantMessengerType();
                    switch ($instantMessengerDataType) {
                        case WhatsappData::getInstantMessengerType():
                            $instantMessengerDataService = $this->_whatsappDataService;
                            break;
                        case FacebookMessengerData::getInstantMessengerType():
                            $instantMessengerDataService = $this->_facebookMessengerDataService;
                            break;
                        case TelegramData::getInstantMessengerType():
                            $instantMessengerDataService = $this->_telegramDataService;
                            break;
                    }
                    if ($instantMessengerDataService !== null) {
                        if(!$responseCalculatedUsingOr) {
                            $returnValue = $actionFunction($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $actionFunctionExtraParameter) && ($returnValue === null || $returnValue);
                        } else {
                            $returnValue = $actionFunction($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $actionFunctionExtraParameter) || ($returnValue !== null && $returnValue);
                        }
                    }
                    unset($instantMessengerDataService, $instantMessengerDataType);
                }
            }
            unset($instantMessengerDataObject);
        }
        return ($returnValue!==null)&&$returnValue;
    }

    /**
     * @param string $encodedJsonString
     * @param InstantMessengerDomainSetting $instantMessengerDomainSetting
     * @return bool
     * @throws InstantMessengerServiceException
     */
    public function extractDomainSettingFromEncodedJson(string $encodedJsonString, &$instantMessengerDomainSetting):bool{
        return $this->_instantMessengerDomainSettingService->extractDomainSettingFromEncodedJson($encodedJsonString, $instantMessengerDomainSetting);
    }

    /**
     * @param InstantMessengerDomainSetting $instantMessengerDomainSetting
     * @return bool
     */
    public function isDomainSettingEmpty(InstantMessengerDomainSetting $instantMessengerDomainSetting):bool{
        return $this->_instantMessengerDomainSettingService->isDomainSettingEmpty($instantMessengerDomainSetting);
    }

    /**
     * @param string $encodedJsonString
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @return bool
     * @throws InstantMessengerServiceException
     */
    public function extractImDataFromEncodedJson(string $encodedJsonString, &$instantMessengerDataArray):bool
    {
        $returnValue = false;
        $arrayCount = count($instantMessengerDataArray);
        if (($instantMessengerDataArray instanceof InstantMessengerDataArray) && ($arrayCount > 0) && !empty($encodedJsonString)) {
            $decodedJsonInstantMessengerSiteManagerData = json_decode($encodedJsonString, false);
            if (JSON_ERROR_NONE === json_last_error()) {
                if (is_array($decodedJsonInstantMessengerSiteManagerData)) {
                    $returnValue = $this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
                        static function($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $extraDataParameter){
                            if ($instantMessengerDataService !== null) {
                                foreach ($extraDataParameter as $instantMessengerData) {
                                    if (!property_exists($instantMessengerData, 'type') || !property_exists($instantMessengerData, 'data')) {
                                        continue;
                                    }
                                    if (empty($instantMessengerData->type) || empty($instantMessengerData->data) || $instantMessengerData->type !== $instantMessengerDataType) {
                                        continue;
                                    }

                                    return $instantMessengerDataService->extractFromInstantMessengerIntegrationDataFromDecodedJson($instantMessengerData->data, $instantMessengerDataObject);
                                }
                            }
                            return false;
                        }, $decodedJsonInstantMessengerSiteManagerData);
                }
            } else {
                $e = new Exception(json_last_error_msg(), json_last_error());
                throw new InstantMessengerServiceException('Unexpected json error on extractFromEncodedJson method of InstantMessengerService.php', $e);
            }
            unset($decodedJsonInstantMessengerSiteManagerData);
        }
        unset($arrayCount);
        return $returnValue;
    }

    /**
     * @param array $httpPostArray
     * @param $instantMessengerDomainSetting
     * @return bool
     */
    public function extractDomainSettingFromHttpPostArray(array $httpPostArray, &$instantMessengerDomainSetting):bool{
        return $this->_instantMessengerDomainSettingService->extractDomainSettingFromHttpPostArray($httpPostArray, $instantMessengerDomainSetting);
    }

    /**
     * @param array $httpPostArray
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @return bool
     */
    public function extractImDataFromHttpPostArray(array $httpPostArray, &$instantMessengerDataArray):bool
    {
        $returnValue = false;
        if (!empty($httpPostArray) && is_array($httpPostArray)) {
            $arrayCount = count($instantMessengerDataArray);
            if (($instantMessengerDataArray instanceof InstantMessengerDataArray) && ($arrayCount > 0) && !empty($httpPostArray) && array_key_exists('plugin', $httpPostArray)) {
                $plugin = $httpPostArray['plugin'];
                if (!empty($plugin) && is_array($plugin) && array_key_exists('instant_messenger_integration', $plugin)) {
                    $instant_messenger_integration = $plugin['instant_messenger_integration'];
                    if (!empty($instant_messenger_integration) && is_array($instant_messenger_integration)) {
                        $returnValue = $this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
                            static function ($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $extraDataParameter) {
                                if (($instantMessengerDataService !== null) && array_key_exists($instantMessengerDataType, $extraDataParameter) && !empty($extraDataParameter[$instantMessengerDataType])) {
                                    return $instantMessengerDataService->extractFromInstantMessengerIntegrationArrayFromHttpPost($extraDataParameter[$instantMessengerDataType], $instantMessengerDataObject);
                                }
                                return false;
                            }, $instant_messenger_integration);
                    }
                    unset($instant_messenger_integration);
                }
                unset($plugin);
            }
            unset($arrayCount);
        }
        return $returnValue;
    }

    /**
     * @param LegacyListing $imInjectedLegacyListing
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @return bool
     */
    public function extractImDataFromInstantMessengerInjectedLegacyClassListing(LegacyListing $imInjectedLegacyListing, &$instantMessengerDataArray):bool
    {
        $returnValue = false;
        if ($imInjectedLegacyListing !== null) {
            $arrayCount = count($instantMessengerDataArray);
            if (($instantMessengerDataArray instanceof InstantMessengerDataArray) && ($arrayCount > 0)) {
                if (property_exists($imInjectedLegacyListing, 'instant_messenger_integration') && $imInjectedLegacyListing->instant_messenger_integration!==null) {
                    $returnValue = $this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
                        static function ($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $extraDataParameter) {
                            if (($instantMessengerDataService !== null) && !empty($extraDataParameter)) {
                                return $instantMessengerDataService->extractFromLegacyClassListingInstantMessengerInjectedObject($extraDataParameter, $instantMessengerDataObject);
                            }
                            return false;
                        }, $imInjectedLegacyListing->instant_messenger_integration);
                }
            }
            unset($arrayCount);
        }
        return $returnValue;
    }

    /**
     * @param int $listingId
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @param ListingInstantMessenger $databaseRegistry
     * @return bool
     * @throws InstantMessengerServiceException
     */
    public function extractImDataFromDatabaseListing($listingId, &$instantMessengerDataArray, &$databaseRegistry):bool
    {
        $returnValue = false;
        if (!empty($listingId) && ($this->_listingInstantMessengerRepository instanceof ListingInstantMessengerRepository)) {
            $arrayCount = count($instantMessengerDataArray);
            if (($instantMessengerDataArray instanceof InstantMessengerDataArray) && ($arrayCount > 0)) {
                /** @var ListingInstantMessenger $databaseRegistry */
                $databaseRegistry = $this->_listingInstantMessengerRepository->findOneBy(['listing' => $listingId]);
                if ($databaseRegistry !== null) {
                    $encodedJsonInstantMessenger = $databaseRegistry->getInstantMessenger();
                    if (!empty($encodedJsonInstantMessenger)) {
                        $this->extractImDataFromEncodedJson($encodedJsonInstantMessenger, $instantMessengerDataArray);
                    }
                    unset($encodedJsonInstantMessenger);
                }
                unset($listingInstantMessenger);
            }
            unset($arrayCount);
        }
        return $returnValue;
    }

    /**
     * @param array $httpPostArray
     * @param array $errorsMessageArray
     * @param InstantMessengerDataClassesArray $instantMessengerClassesArray
     * @return bool
     */
    public function validateImDataFromHttpPostArray(array $httpPostArray, array &$errorsMessageArray, InstantMessengerDataClassesArray $instantMessengerClassesArray):bool
    {
        $returnValue = null;
        if (!empty($httpPostArray) && is_array($httpPostArray) &&
            isset($params['error_messages_array']) && is_array(['error_messages_array'])) {
            $arrayCount = count($instantMessengerClassesArray);
            if (($arrayCount > 0) && array_key_exists('plugin', $httpPostArray)) {
                $plugin = $httpPostArray['plugin'];
                if (!empty($plugin) && is_array($plugin) && array_key_exists('instant_messenger_integration', $plugin)) {
                    $instant_messenger_integration = $plugin['instant_messenger_integration'];
                    if (!empty($instant_messenger_integration) && is_array($instant_messenger_integration)) {
                        $dataToAction = array('return_value'=> &$returnValue, 'srcObject' => &$this, 'instant_messenger_integration'=>&$instant_messenger_integration,'errors_message_array'=>&$errorsMessageArray);
                        if($this->executeActionOverInstantMessengerDataClassesArrayItemsTypes($instantMessengerClassesArray,
                            static function($instantMessengerDataType,$instantMessengerClass,&$actionFunctionExtraParameter){
                                $instantMessengerDataService = null;
                                switch ($instantMessengerDataType) {
                                    case WhatsappData::getInstantMessengerType():
                                        $instantMessengerDataService = $actionFunctionExtraParameter['srcObject']->_whatsappDataService;
                                        break;
                                    case FacebookMessengerData::getInstantMessengerType():
                                        $instantMessengerDataService = $actionFunctionExtraParameter['srcObject']->_facebookMessengerDataService;
                                        break;
                                    case TelegramData::getInstantMessengerType():
                                        $instantMessengerDataService = $actionFunctionExtraParameter['srcObject']->_telegramDataService;
                                        break;
                                }
                                if ($instantMessengerDataService !== null) {
                                    $actionFunctionExtraParameter['return_value'] = $instantMessengerDataService->validateDataFromHttpPostArray($actionFunctionExtraParameter['instant_messenger_integration'], $actionFunctionExtraParameter['errors_message_array'])&&($actionFunctionExtraParameter['return_value']===null || $actionFunctionExtraParameter['return_value']);
                                }
                                return true;
                            },$dataToAction)) {
                            $errorsMessageArray = $dataToAction['errors_message_array'];
                            $returnValue = $dataToAction['return_value'];
                        }
                    }
                    unset($instant_messenger_integration);
                }
                unset($plugin);
            }
            unset($arrayCount);
        }
        return ($returnValue!==null)&&$returnValue;
    }

    /**
     * @param InstantMessengerDataClassesArray $instantMessengerClassesArray
     * @param callable $actionFunction
     * @param mixed|null $actionFunctionExtraParameter
     * @return bool
     */
    protected function executeActionOverInstantMessengerDataClassesArrayItemsTypes(InstantMessengerDataClassesArray $instantMessengerClassesArray, $actionFunction, &$actionFunctionExtraParameter = null): bool {
        $returnValue = null;
        if(is_callable($actionFunction)) {
            if ($instantMessengerClassesArray !== null) {
                $arrayCount = count($instantMessengerClassesArray);
                if ($arrayCount > 0) {
                    foreach ($instantMessengerClassesArray as $instantMessengerClass) {
                        $tempInstantMessengerClassObject = new $instantMessengerClass();
                        /** @var InstantMessengerData $tempInstantMessengerClassObject */
                        /** @var InstantMessengerDataService $instantMessengerDataService */
                        $instantMessengerDataService = null;
                        $instantMessengerDataType = $tempInstantMessengerClassObject::getInstantMessengerType();
                        unset($tempInstantMessengerClassObject);
                        $returnValue = $actionFunction($instantMessengerDataType,$instantMessengerClass,$actionFunctionExtraParameter)&&($returnValue===null || $returnValue);
                        unset($instantMessengerDataType);
                    }
                }
            }
        }
        return ($returnValue!==null)&&$returnValue;
    }

    /**
     * @param string $encodedJsonString
     * @param array $httpPostArray
     * @param InstantMessengerDataClassesArray $instantMessengerClassesArray
     * @return bool
     * @throws InstantMessengerServiceException
     */
    public function hasImDataDifferenceBetweenHttpPostArrayAndEncodedJson(string $encodedJsonString, array $httpPostArray, InstantMessengerDataClassesArray $instantMessengerClassesArray):bool {
        if ($instantMessengerClassesArray !== null) {
            $arrayCount = count($instantMessengerClassesArray);
            if ($arrayCount > 0) {
                $instantMessengerData = array('fromHttpPostArray' => new InstantMessengerDataArray(),'fromEncodedJson' => new InstantMessengerDataArray());
                if($this->executeActionOverInstantMessengerDataClassesArrayItemsTypes($instantMessengerClassesArray,
                    static function($instantMessengerDataType,$instantMessengerClass,&$actionFunctionExtraParameter){
                        switch ($instantMessengerDataType) {
                            case WhatsappData::getInstantMessengerType():
                                $actionFunctionExtraParameter['fromHttpPostArray']->append(new WhatsappData());
                                $actionFunctionExtraParameter['fromEncodedJson']->append(new WhatsappData());
                                break;
                            case FacebookMessengerData::getInstantMessengerType():
                                $actionFunctionExtraParameter['fromHttpPostArray']->append(new FacebookMessengerData());
                                $actionFunctionExtraParameter['fromEncodedJson']->append(new FacebookMessengerData());
                                break;
                            case TelegramData::getInstantMessengerType():
                                $actionFunctionExtraParameter['fromHttpPostArray']->append(new TelegramData());
                                $actionFunctionExtraParameter['fromEncodedJson']->append(new TelegramData());
                                break;
                        }
                        return true;
                    },$instantMessengerData)){
                    $this->extractImDataFromHttpPostArray($httpPostArray, $instantMessengerData['fromHttpPostArray']);
                    $this->extractImDataFromEncodedJson($encodedJsonString, $instantMessengerData['fromEncodedJson']);
                    return $this->hasImDataDifferenceBetweenInstantMessengerDataArrays($instantMessengerData['fromHttpPostArray'], $instantMessengerData['fromEncodedJson']);
                }
            }
        }
        return false;
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @param array $httpPostArray
     * @return bool
     */
    public function hasImDataDifferenceBetweenInstantMessengerDataArrayAndHttpPostArray(InstantMessengerDataArray $instantMessengerDataArray, array $httpPostArray):bool {
        if ($instantMessengerDataArray !== null) {
            $arrayCount = count($instantMessengerDataArray);
            if ($arrayCount > 0) {
                $dataArrayFromHttpPostArray = new InstantMessengerDataArray();
                if($this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
                    static function($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, &$actionFunctionExtraParameter){
                        switch ($instantMessengerDataType) {
                            case WhatsappData::getInstantMessengerType():
                                $actionFunctionExtraParameter->append(new WhatsappData());
                                break;
                            case FacebookMessengerData::getInstantMessengerType():
                                $actionFunctionExtraParameter->append(new FacebookMessengerData());
                                break;
                            case TelegramData::getInstantMessengerType():
                                $actionFunctionExtraParameter->append(new TelegramData());
                                break;
                        }
                        return true;
                    },$dataArrayFromHttpPostArray)){
                    $this->extractImDataFromHttpPostArray($httpPostArray, $dataArrayFromHttpPostArray);
                    return $this->hasImDataDifferenceBetweenInstantMessengerDataArrays($dataArrayFromHttpPostArray, $instantMessengerDataArray);
                }
                unset($dataArrayFromHttpPostArray);
            }
        }
        return false;
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @param string $encodedJsonString
     * @return bool
     * @throws InstantMessengerServiceException
     */
    public function hasImDataDifferenceBetweenInstantMessengerDataArrayAndEncodedJsonString(InstantMessengerDataArray $instantMessengerDataArray, string $encodedJsonString):bool {
        if ($instantMessengerDataArray !== null) {
            $arrayCount = count($instantMessengerDataArray);
            if ($arrayCount > 0) {
                $dataArrayFromEncodedJsonString = new InstantMessengerDataArray();
                if($this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
                    static function($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, &$actionFunctionExtraParameter){
                        switch ($instantMessengerDataType) {
                            case WhatsappData::getInstantMessengerType():
                                $actionFunctionExtraParameter->append(new WhatsappData());
                                break;
                            case FacebookMessengerData::getInstantMessengerType():
                                $actionFunctionExtraParameter->append(new FacebookMessengerData());
                                break;
                            case TelegramData::getInstantMessengerType():
                                $actionFunctionExtraParameter->append(new TelegramData());
                                break;
                        }
                        return true;
                    },$dataArrayFromEncodedJsonString)){
                    $this->extractImDataFromEncodedJson($encodedJsonString, $dataArrayFromEncodedJsonString);
                    return $this->hasImDataDifferenceBetweenInstantMessengerDataArrays($dataArrayFromEncodedJsonString,$instantMessengerDataArray);
                }
                unset($dataArrayFromEncodedJsonString);
            }
        }
        return false;
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerSourceDataArray
     * @param InstantMessengerDataArray $instantMessengerComparisonDataArray
     * @return bool
     */
    public function hasImDataDifferenceBetweenInstantMessengerDataArrays(InstantMessengerDataArray $instantMessengerSourceDataArray, InstantMessengerDataArray $instantMessengerComparisonDataArray):bool
    {
        $returnValue = null;
        if ($instantMessengerSourceDataArray !== null && $instantMessengerComparisonDataArray !== null) {
            $dataArrayFromEncodedJsonStringCount = count($instantMessengerSourceDataArray);
            $dataArrayFromHttpPostArrayCount = count($instantMessengerComparisonDataArray);
            if ($dataArrayFromEncodedJsonStringCount !== $dataArrayFromHttpPostArrayCount) {
                $returnValue = true;
            } else {
                $instantMessengerData = array('comparison_im_data_array' => $instantMessengerComparisonDataArray,'return_value' => &$returnValue);
                if($this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerSourceDataArray,
                    static function($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, &$actionFunctionExtraParameter){
                        if ($instantMessengerDataService !== null) {
                            /** @var InstantMessengerDataArray $comparisonImDataArray */
                            $comparisonImDataArray = $actionFunctionExtraParameter['comparison_im_data_array'];
                            $instantMessengerDataToCompare = null;
                            foreach ($comparisonImDataArray as $comparisonImData) {
                                /** @var InstantMessengerData $comparisonImData */
                                if ($comparisonImData::getInstantMessengerType() === $instantMessengerDataType) {
                                    $instantMessengerDataToCompare = $comparisonImData;
                                    break;
                                }
                            }
                            unset($comparisonImDataArray);
                            $actionFunctionExtraParameter['return_value'] = ($instantMessengerDataService->isDataEqual($instantMessengerDataObject, $instantMessengerDataToCompare)&&($actionFunctionExtraParameter['return_value'] === null || $actionFunctionExtraParameter['return_value']));
                            return true;
                        }
                        return false;
                    },$instantMessengerData)){
                    $returnValue = !$instantMessengerData['return_value'];
                }
                unset($instantMessengerData);
            }
            unset($dataArrayFromEncodedJsonStringCount,$dataArrayFromHttpPostArrayCount);
        } else {
            $returnValue = !($instantMessengerSourceDataArray === null && $instantMessengerComparisonDataArray === null);
        }
        return ($returnValue !== null) && $returnValue;
    }

    /**
     * @param InstantMessengerDomainSetting $instantMessengerDomainSettingSource
     * @param InstantMessengerDomainSetting $instantMessengerDomainSettingComparison
     * @return bool
     */
    public function hasDifferenceBetweenInstantMessengerDomainSettings(InstantMessengerDomainSetting $instantMessengerDomainSettingSource,InstantMessengerDomainSetting $instantMessengerDomainSettingComparison):bool
    {
        return !$this->_instantMessengerDomainSettingService->isInstantMessengerDomainSettingsEqual($instantMessengerDomainSettingSource,$instantMessengerDomainSettingComparison);
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @param InstantMessengerButtonData $instantMessengerButtonData
     * @return bool
     */
    public function extractInstantMessengerButtonDataFromInstantMessengerDataArray(InstantMessengerDataArray $instantMessengerDataArray, InstantMessengerButtonData $instantMessengerButtonData):bool
    {
        return $this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
            static function ($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $extraDataParameter) {
                if (($instantMessengerDataService !== null) && ($extraDataParameter instanceof InstantMessengerButtonData) && !empty($extraDataParameter->type)) {
                    if ($extraDataParameter->type === $instantMessengerDataType) {
                        $instantMessengerDataService->extractInstantMessengerButtonDataFromInstantMessengerData($instantMessengerDataObject, $extraDataParameter);
                        return true;
                    }
                }
                return false;
            }, $instantMessengerButtonData, true);
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @param InstantMessengerLinkButtonData $instantMessengerButtonData
     * @return bool
     */
    public function extractInstantMessengerLinkButtonDataFromInstantMessengerDataArray(InstantMessengerDataArray $instantMessengerDataArray, InstantMessengerLinkButtonData $instantMessengerButtonData):bool
    {
        return $this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
            static function ($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $extraDataParameter) {
                if (($instantMessengerDataService !== null) && ($extraDataParameter instanceof InstantMessengerLinkButtonData) && !empty($extraDataParameter->type)) {
                    if ($extraDataParameter->type === $instantMessengerDataType) {
                        $instantMessengerDataService->extractInstantMessengerLinkButtonDataFromInstantMessengerData($instantMessengerDataObject, $extraDataParameter);
                        return true;
                    }
                }
                return false;
            }, $instantMessengerButtonData, true);
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @param InstantMessengerLinkButtonDataArray $instantMessengerLinkButtonDataArray
     * @return bool
     */
    public function extractInstantMessengerLinkButtonDataArrayFromInstantMessengerDataArray(InstantMessengerDataArray $instantMessengerDataArray, InstantMessengerLinkButtonDataArray $instantMessengerLinkButtonDataArray):bool
    {
        return $this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
            static function ($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, $extraDataParameter) {
                if (($instantMessengerDataService !== null) && ($extraDataParameter instanceof InstantMessengerLinkButtonDataArray)) {
                    $instantMessengerLinkButtonData = new InstantMessengerLinkButtonData();
                    if ($instantMessengerDataService->extractInstantMessengerLinkButtonDataFromInstantMessengerData($instantMessengerDataObject, $instantMessengerLinkButtonData)) {
                        $extraDataParameter->append($instantMessengerLinkButtonData);
                        return true;
                    }
                }
                return false;
            }, $instantMessengerLinkButtonDataArray,true);
    }

    /**
     * @param InstantMessengerDataArray $instantMessengerDataArray
     * @return bool
     */
    public function isImDataEmpty(InstantMessengerDataArray $instantMessengerDataArray): bool
    {
        $returnValue = true;
        if ($instantMessengerDataArray !== null) {
            $instantMessengerDataArrayCount = count($instantMessengerDataArray);
            if ($instantMessengerDataArrayCount > 0) {
                $this->executeActionOverInstantMessengerDataObjectAndService($instantMessengerDataArray,
                    static function($instantMessengerDataType, $instantMessengerDataObject, $instantMessengerDataService, &$actionFunctionExtraParameter){
                        if ($instantMessengerDataService !== null) {
                            $actionFunctionExtraParameter = $actionFunctionExtraParameter && $instantMessengerDataService->isDataEmpty($instantMessengerDataObject);
                            return true;
                        }
                        return false;
                    },$returnValue);
            }
            unset($instantMessengerDataArrayCount);
        }
        return $returnValue;
    }
}
