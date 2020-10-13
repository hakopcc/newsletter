<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal;


interface InstantMessengerData
{
    public static function getInstantMessengerType():string;
    public function isDataEmpty():bool;
    public function addListingValueObject($objectToReceiveValueObject):void;
}
