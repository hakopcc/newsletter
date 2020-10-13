<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity\Internal;


class WhatsappData implements InstantMessengerData
{
    public $country_code;

    public $number;

    public static function getInstantMessengerType(): string
    {
        return 'whatsapp';
    }

    public function isDataEmpty(): bool
    {
        return empty($this->country_code) && empty($this->number);
    }

    public function addListingValueObject($objectToReceiveValueObject): void
    {
        $objectToReceiveValueObject->whatsapp = (object)[
            'country_code_value' => $this->country_code,
            'number_value' => $this->number,
        ];
    }
}
