<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal;


class TelegramData implements InstantMessengerData
{
    public $nickname;

    public static function getInstantMessengerType(): string
    {
        return 'telegram';
    }

    public function isDataEmpty(): bool
    {
        return empty($this->nickname);
    }

    public function addListingValueObject($objectToReceiveValueObject): void
    {
        $objectToReceiveValueObject->telegram = (object)[
            'nickname_value' => $this->nickname,
        ];
    }
}
