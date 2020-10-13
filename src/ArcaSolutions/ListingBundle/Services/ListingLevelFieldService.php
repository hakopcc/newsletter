<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingLevelFieldService
 * @package ArcaSolutions\ListingBundle\Services
 */
class ListingLevelFieldService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ListingLevelService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ListingLevel[]
     */
    public function getListingLevelsByFieldName($fieldName)
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelsByFieldName($fieldName);
    }

    /**
     * @param $fieldId
     * @return mixed
     */
    public function getListingLevelsByFieldId($fieldId)
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelsByFieldId($fieldId);
    }

    /**
     * @param $fieldId
     * @return mixed
     */
    public function getListingLevelsByGroupId($groupId)
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelsByGroupId($groupId);
    }

    /**
     * @param $level
     * @return ListingLevel[]|object[]
     */
    public function getListingLevelFieldsNameByLevel($level)
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelFieldsNameByLevel($level);
    }

    /**
     * @param string $fieldName
     * @param bool $members
     * @param int $fieldId
     * @param int $groupId
     * @param bool $toolTip
     * @param null $itemId
     * @return string
     */
    public function getBlockFieldListingLevelText($fieldName, $members = null, $fieldId = null, $groupId = null, $toolTip = false, $itemId = null)
    {
        $levelsString = '';

        $translator = $this->container->get('translator');

        if(!$members) {
            $locale = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        } else {
            $locale = null;
        }

        if($groupId !== null) {
            $levels = $this->getListingLevelsByGroupId($groupId);
        } elseif ($fieldId !== null) {
            $levels = $this->getListingLevelsByFieldId($fieldId);
        } else {
            $levels = $this->getListingLevelsByFieldName($fieldName);
        }

        foreach($levels as $key => $level) {
            if(empty($levelsString)) {
                $levelsString .= $level['name'];
                continue;
            }

            end($levels);
            if($key === key($levels)) {
                $levelsString .= ' ' . $translator->trans('and', [], 'messages', $locale) . ' ' . $level['name'];
            } else {
                $levelsString .= ', ' . $level['name'];
            }
        }

        if($members && $itemId) {
            return $this->container->get('translator')->trans('%start_upgrade% Upgrade your plan %end_upgrade% and get access to this content', ['%start_upgrade%' => ($toolTip ? '' : '<a data-toggle="modal" href="#modal-upgrade" class="link">'), '%end_upgrade%' => ($toolTip ? '' : '</a>')], 'administrator', $locale);
        }

        if(!empty($levelsString)) {
            return $this->container->get('translator')->transChoice('Content available only for %levels% levels.', count($levels), ['%levels%' => $levelsString], 'administrator', $locale);
        }

        return null;
    }

    /**
     * @param $templateId
     * @return array
     */
    public function getListingLevelFieldsByTemplate($templateId)
    {
        $levelfields['listingtfield_id'] = $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelFieldIdsByTemplate($templateId);
        $levelfields['listingtfieldgroup_id'] = $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelFieldGroupIdsByTemplate($templateId);

        return $levelfields;
    }

    /**
     * @param $templateId
     * @param $level
     * @return mixed
     */
    public function getListingLevelFieldsByTemplateAndLevel($templateId, $level)
    {
        $levelfields['listingtfield_id'] = $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelFieldIdsByTemplateAndLevel($templateId, $level);
        $levelfields['listingtfieldgroup_id'] = $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getListingLevelFieldGroupIdsByTemplateAndLevel($templateId, $level);

        return $levelfields;
    }
}
