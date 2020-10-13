<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal;


use ArrayObject;

class InstantMessengerDataArray extends ArrayObject
{
    /**
     * @param mixed $key
     * @param mixed $val
     * @return void
     */
    public function offsetSet($key, $val):void {
        if ($val instanceof InstantMessengerData) {
            parent::offsetSet($key, $val);
            return;
        }
        throw new \InvalidArgumentException('Value must be a InstantMessengerData');
    }

    public function isAllItemsWithEmptyProperties():bool{
        $returnValue = true;
        foreach ($this as $thisVal) {
            /** @var InstantMessengerData $thisVal */
            $returnValue = $returnValue && $thisVal->isDataEmpty();
            if(!$returnValue){
                break;
            }
        }
        return $returnValue;
    }

    public function convertToArrayToBeJsonEncoded():array{
        $unencodedJsonArray = array();
        foreach ($this as $thisVal){
            /** @var InstantMessengerData $thisVal */
            $unencodedJsonArray[] = (object) [
                'type' => $thisVal::getInstantMessengerType(),
                'data' => $thisVal
            ];
        }
        return $unencodedJsonArray;
    }

    /**
     * @return object|null
     */
    public function convertToListingValuesObject(){
        $instantMessengerIntegrationData = (object)[];
        $valueAdded = false;
        foreach ($this as $thisVal){
            /** @var InstantMessengerData $thisVal */
            $thisVal->addListingValueObject($instantMessengerIntegrationData);
            $valueAdded = true;
        }
        return $valueAdded?$instantMessengerIntegrationData:null;
    }
}
