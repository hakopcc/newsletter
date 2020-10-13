<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Services;

use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerButtonData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerData;
use ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal\InstantMessengerLinkButtonData;
use Symfony\Component\Translation\TranslatorInterface;

abstract class InstantMessengerDataService
{
    /** @var TranslatorInterface $translator */
    protected $translator;

    /** @var string $lang */
    private $lang = 'en';

    public function __construct(TranslatorInterface $translator, $lang = 'en'){
        $this->translator = $translator;
    }

    public function setLang($lang){
        $this->lang = $lang;
    }

    /**
     * Helper function: Include in the form errors array the provided error message, considering translation.
     *
     * @param $errors - Form errors array
     * @param $message - Error message
     */
    protected function addFormErrorMessage(&$errors, $message):void
    {
        if (!empty($message) && is_array($errors)) {
            $errorMessage = '&#149;&nbsp;';
            if (!empty($this->translator)) {
                $messageSuffix = /** @Ignore */$this->translator->trans($message,[],'messages',$this->lang);
                $errorMessage .= $messageSuffix;
                unset($messageSuffix);
            } else {
                $errorMessage .= $message;
            }
            $errors[] = $errorMessage;
            unset($errorMessage);
        }
    }

    /**
     * @param array $instantMessengerIntegrationArrayFromHttpPost
     * @param InstantMessengerData $instantMessengerDataObject
     * @return bool
     */
    abstract public function extractFromInstantMessengerIntegrationArrayFromHttpPost(array $instantMessengerIntegrationArrayFromHttpPost, &$instantMessengerDataObject):bool;

    /**
     * @param InstantMessengerData $instantMessengerData
     * @param InstantMessengerButtonData $instantMessengerButtonData
     * @return bool
     */
    abstract public function extractInstantMessengerButtonDataFromInstantMessengerData(InstantMessengerData $instantMessengerData, InstantMessengerButtonData $instantMessengerButtonData):bool;

    /**
     * @param InstantMessengerData $instantMessengerData
     * @param InstantMessengerLinkButtonData $instantMessengerButtonData
     * @return bool
     */
    abstract public function extractInstantMessengerLinkButtonDataFromInstantMessengerData(InstantMessengerData $instantMessengerData, InstantMessengerLinkButtonData $instantMessengerButtonData):bool;

    /**
     * @param mixed $injectedLegacyListingImObject
     * @param InstantMessengerData $instantMessengerDataObject
     * @return bool
     */
    abstract public function extractFromInjectedLegacyClassListingInstantMessengerObject($injectedLegacyListingImObject, &$instantMessengerDataObject):bool;

    /**
     * @param array $httpPostArray
     * @param $errorsMessageArray
     * @return bool
     */
    abstract public function validateDataFromHttpPostArray(array $httpPostArray, &$errorsMessageArray):bool;

    /**
     * @param mixed $instantMessengerIntegrationDataFromDecodedJson
     * @param InstantMessengerData $instantMessengerDataObject
     * @return bool
     */
    abstract public function extractFromInstantMessengerIntegrationDataFromDecodedJson($instantMessengerIntegrationDataFromDecodedJson, &$instantMessengerDataObject):bool;

    /**
     * @param InstantMessengerData $subject
     * @param InstantMessengerData $comparisonSubject
     * @return bool
     */
     public function isDataEqual(InstantMessengerData $subject, InstantMessengerData $comparisonSubject):bool{
         $returnValue = false;
         if($comparisonSubject===null && $subject===null){
             $returnValue = true;
         } elseif ($comparisonSubject!==null && $subject!==null){
             $subjectType = $subject->getInstantMessengerType();
             $comparisonSubjectType = $comparisonSubject->getInstantMessengerType();
             if($subjectType===$comparisonSubjectType){
                 $returnValue = true;
             }
             unset($subjectType,$comparisonSubjectType);
         } else {
             $returnValue = false;
         }
         return $returnValue;
     }

    /**
     * @param InstantMessengerData $instantMessengerData
     * @param InstantMessengerButtonData $instantMessengerButtonData
     * @return bool
     */
    protected function extractIconSvgRawContentFromInstantMessengerData(InstantMessengerData $instantMessengerData,InstantMessengerButtonData $instantMessengerButtonData): bool
    {
        $returnValue = false;
        $iconSvgRawContent = null;
        $instantMessengerIconSvgFilePath = __DIR__.'/../Resources/assets/'.$instantMessengerData::getInstantMessengerType() .'_icon.svg';
        if (!file_exists($instantMessengerIconSvgFilePath)) {
            $instantMessengerIconSvgFilePath = __DIR__.'/../Resources/assets/unknow_icon.svg';
        }
        if (file_exists($instantMessengerIconSvgFilePath)) {
            $instantMessengerIconSvgFileContent = file_get_contents($instantMessengerIconSvgFilePath);
            if (!empty($instantMessengerIconSvgFileContent)) {
                $iconSvgRawContent = $instantMessengerIconSvgFileContent;
            }
            unset($instantMessengerIconSvgFileContent);
        }
        unset($instantMessengerIconSvgFilePath);

        if($iconSvgRawContent!==null){
            $instantMessengerButtonData->iconSvgRawContent = $iconSvgRawContent;
            $returnValue = true;
        }
        return $returnValue;
    }

    /**
     * @param InstantMessengerData $instantMessengerData
     * @return bool
     */
    abstract public function isDataEmpty(InstantMessengerData $instantMessengerData):bool;
}
