<?php

namespace ArcaSolutions\ApiBundle\Documents;

use ArcaSolutions\ClassifiedBundle\Entity\Classified;
use ArcaSolutions\ClassifiedBundle\Entity\Internal\ClassifiedLevelFeatures;
use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Listing;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Process entity with fields that are not allowed by its detail level setting null as value
 *
 * @package ArcaSolutions\ApiBundle\Documents
 */
class ModuleLevelDocument
{
    /**
     * Maps the itemLevelFeature field with the fields of the item
     * @var array
     */
    private $map;

    /**
     * @param string $module
     */
    public function __construct($module)
    {
        if (!in_array($module, ['listing', 'event', 'classified'])) {
            throw new InvalidParameterException();
        }

        $getModuleLevelMap = 'get'.ucfirst($module).'LevelMap';

        $this->map = $this->$getModuleLevelMap();
    }

    /**
     * Return the item with the fields that are not allowed by its detail level set as null
     *
     * @TODO Keep this map updated with ListingLevelFeatures
     * @param Listing | Classified | Event $item
     * @param ListingLevelFeatures | ClassifiedLevelFeatures | EventLevelFeatures $itemDetailLevel
     * @return mixed
     */
    public function applyModuleLevel($item, $itemDetailLevel)
    {
        foreach ($this->map as $feature => $attributes) {
            $hasFeature = 'has'.ucfirst($feature);

            if (!$itemDetailLevel->$hasFeature) {
                foreach ($attributes as $attribute) {
                    $setAttribute = 'set'.ucfirst($attribute);

                    $item->$setAttribute(null);
                }
            }
        }

        return $item;
    }

    /**
     * Get listing level map
     *
     * @TODO keep this map updated with ListingLevelFeatures
     * @return array
     */
    protected function getListingLevelMap()
    {
        $levelItems = [
            'review'             => ['avgReview', 'reviewsTotal'],
            'email'              => ['email'],
            'URL'                => ['url'],
            'phone'              => ['phone'],
            'additionalPhone'    => ['labelAdditionalPhone', 'additionalPhone'],
            'video'              => ['videoUrl', 'videoSnippet', 'videoDescription'],
            'additionalFiles'    => ['attachmentCaption', 'attachmentFile'],
            'summaryDescription' => ['description'],
            'longDescription'    => ['longDescription'],
            'hoursOfWork'        => ['hoursWork'],
            'locationReference'  => ['locations'],
            'badges'             => ['choices'],
            'socialNetworking'   => ['socialNetwork'],
            'featureInformation' => ['features'],
            'logoImage'          => ['logoImageUrl'],
        ];

        /* ModStores Hooks */
        HookFire("modulelevelapi_before_return_listingmap", [
            "levelItems" => &$levelItems
        ]);

        return $levelItems;
    }

    /**
     * Get event level map
     *
     * @TODO keep this map updated with EventLevelFeatures
     * @return array
     */
    protected function getEventLevelMap()
    {
        $levelItems = [
            'email'              => ['email'],
            'URL'                => ['url'],
            'phone'              => ['phone'],
            'video'              => ['videoUrl', 'videoSnippet'],
            'summaryDescription' => ['description'],
            'longDescription'    => ['longDescription'],
            'contactName'        => ['contactName'],
            'eventTime'          => ['startTime', 'endTime'],
            'facebookPage'       => ['facebookPage'],
        ];

        /* ModStores Hooks */
        HookFire("modulelevelapi_before_return_eventmap", [
            "levelItems" => &$levelItems
        ]);

        return $levelItems;
    }

    /**
     * Get classified level map
     *
     * @TODO keep this map updated with ClassifiedLevelFeatures
     * @return array
     */
    protected function getClassifiedLevelMap()
    {
        $levelItems = [
            'URL'                => ['url'],
            'video'              => ['videoUrl', 'videoSnippet', 'videoDescription'],
            'additionalFiles'    => ['attachmentCaption', 'attachmentFile'],
            'summaryDescription' => ['summarydesc'],
            'longDescription'    => ['detaildesc'],
            'contactName'        => ['contactName'],
            'Phone'              => ['phone'],
            'Email'              => ['email'],
            'classifiedPrice'    => ['classifiedPrice'],
        ];

        /* ModStores Hooks */
        HookFire("modulelevelapi_before_return_classifiedmap", [
            "levelItems" => &$levelItems
        ]);

        return $levelItems;
    }
}
