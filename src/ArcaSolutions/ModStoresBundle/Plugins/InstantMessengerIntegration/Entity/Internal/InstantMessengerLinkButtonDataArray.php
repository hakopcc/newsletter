<?php
namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal;

use ArrayObject;
use InvalidArgumentException;
use Symfony\Component\Security\Acl\Exception\Exception;

class InstantMessengerLinkButtonDataArray extends ArrayObject
{
    /**
     * @param mixed $key
     * @param mixed $val
     * @return void
     */
    public function offsetSet($key, $val):void {
        try {
            $tempInstantMessengerClassObject = new $val();
            if ($tempInstantMessengerClassObject instanceof InstantMessengerLinkButtonData) {
                parent::offsetSet($key, $val);
                return;
            }
            unset($tempInstantMessengerClassObject);
            throw new InvalidArgumentException('Value must be a InstantMessengerLinkButtonData implemented fully qualified class name');
        } catch (Exception $e){
            throw new InvalidArgumentException('Value must be a InstantMessengerLinkButtonData implemented fully qualified class name', null, $e);
        }
    }
}
