<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal;


class FacebookMessengerData implements InstantMessengerData
{
    public $user_id;
    public $caption;

    public static function getInstantMessengerType(): string
    {
        return 'messenger';
    }

    public function isDataEmpty(): bool
    {
        return empty($this->user_id) && empty($this->caption);
    }

    public function addListingValueObject($objectToReceiveValueObject): void
    {
        $objectToReceiveValueObject->messenger = (object)[
            'user_id_value' => $this->user_id,
            'caption_value' => $this->caption
        ];
    }
}
