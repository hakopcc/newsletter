<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ListingTFieldService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ListingTemplate $listingTemplate
     * @param ReferenceRepository|null $referenceRepository
     */
    public function createDefaultListingTemplateFields(ListingTemplate $listingTemplate, ReferenceRepository $referenceRepository = null)
    {
        $repository = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField');

        $em = $this->container->get('doctrine')->getManager();

        /* These are the standard data of the system */
        $standardInserts = [
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::ADDITIONAL_PHONE
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::ATTACHMENT_FILE
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::VIDEO
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::PHONE
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::IMAGES
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::BADGES
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::SOCIAL_NETWORK
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::EMAIL
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::URL
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::DESCRIPTION
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::LONG_DESCRIPTION
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::LOCATIONS
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::FEATURES
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::DEALS
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::CLASSIFIEDS
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::REVIEW
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::HOURS_WORK
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::COVER_IMAGE
            ],
            [
                'fieldType' => ListingTField::DEFAULT_TYPE,
                'field'     => ListingTField::LOGO_IMAGE
            ],
        ];

        /* ModStores Hooks */
        HookFire("listingtfieldservice-createdefaultlistingtemplatefields_after_setstandardinsertsarray", [
            'standard_to_insert_fields_array'    => &$standardInserts,
            'target_listing_template'            => &$listingTemplate,
            'reference_repository'               => &$referenceRepository
        ]);
        $standardFieldsInserted = array();
        $needFlush = false;
        foreach ($standardInserts as $listingTField) {
            $query = $repository->findOneBy([
                'label'             => $listingTField['field'],
                'fieldType'         => $listingTField['fieldType'],
                'listingTemplateId' => $listingTemplate->getId()
            ]);

            if (empty($query)) {
                $templateField = new ListingTField();
                $templateField->setListingTemplate($listingTemplate);
                $templateField->setLabel($listingTField['field']);
                $templateField->setFieldType($listingTField['fieldType']);
                $em->persist($templateField);
                $standardFieldsInserted[] = $listingTField;
                $needFlush = true;
            } else {
                $templateField = $query;
            }

            if($referenceRepository !== null) {
                $referenceRepository->addReference('FIELD_' . $templateField->getLabel(), $templateField);
            }
        }
        if($needFlush) {
            $em->flush();
        }

        /* ModStores Hooks */
        HookFire("listingtfieldservice-createdefaultlistingtemplatefields_before_return", [
            'standard_to_insert_fields_array'    => $standardInserts,
            'standard_inserted_fields_array'    => $standardFieldsInserted,
            'target_listing_template'   => &$listingTemplate,
            'reference_repository'      => &$referenceRepository
        ]);
    }

    /**
     * @param $templateId
     * @param $linkedListingField
     * @param $listingId
     * @return string
     */
    public function renderLinkedListingTabs($templateId, $listingId, $level, $linkedListingField = null)
    {
        $linkedListings = $this->container->get('listingtemplatefield.service')->getLinkedListings($templateId);

        $linkedListingTabsBlock = '';

        if(!empty($linkedListings)) {
            foreach ($linkedListings as $linkedListing) {
                $activeTab = (!empty($linkedListingField) && $linkedListingField->getId() === $linkedListing->getId()) ? 'class=active' : '';

                $levelQuantity = $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevelField')->getQuantityByTemplateFieldIdAndLevel($linkedListing->getId(), $level);

                if(!empty($levelQuantity['quantity'])) {
                    $linkedListingTabsBlock .= $this->container->get('templating')->render('@Listing/listingForm/linkedListingTabs.html.twig', [
                        'activeTab' => $activeTab,
                        'listingId' => $listingId,
                        'linkedListing' => $linkedListing,
                        'templateId' => $templateId
                    ]);
                }
            }
        }

        return $linkedListingTabsBlock;
    }

    /**
     * @param $templateId
     * @param $levelFields
     * @param null $listingField
     * @param null $members
     * @param null $itemId
     * @return string
     * @throws \Twig_Error
     */
    public function renderListingTemplateFields($templateId, $levelFields, $listingField = null, $members = null, $itemId = null)
    {
        $listingLevelFieldService = $this->container->get('listinglevelfield.service');

        $listingTFields = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->getTemplateCustomFields($templateId);

        $listingTemplateFieldBlock = '';
        $checkboxBlock = [];
        $textFieldBlock = [];
        $checkListBlockMessage = [];
        $textFieldGroupMessage = [];
        $checkboxGroups = [];
        $textFieldGroup = [];
        $showCheckList = [];
        $showTextFieldGroup = [];

        foreach ($listingTFields as $field) {
            if(!empty($levelFields['listingtfield_id']) && is_array($levelFields['listingtfield_id'])) {
                $showField = in_array($field['id'], $levelFields['listingtfield_id']) || in_array($field['id'], array_column($levelFields['listingtfield_id'], 'listingTFieldId'));
            } else {
                $showField = false;
            }

            $blockMessage = $listingLevelFieldService->getBlockFieldListingLevelText(null, $members, $field['id'], null, false, $itemId);

            if ($field['fieldType'] === 'dropdown') {
                $listingTemplateFieldBlock .= $this->renderDropdownField($field, $listingField, $showField, $blockMessage);
            } elseif ($field['fieldType'] === 'checkbox') {
                if (!empty($field['groupId'])) {
                    if (isset($checkboxBlock[$field['groupId']])) {
                        $checkboxBlock[$field['groupId']] .= $this->renderCheckboxField($field, $listingField);
                    } else {
                        $checkboxBlock[$field['groupId']] = $this->renderCheckboxField($field, $listingField);
                    }

                    if(empty($checkboxGroups[$field['groupId']] )) {
                        $checkListBlockMessage[$field['groupId']] = $listingLevelFieldService->getBlockFieldListingLevelText(null, $members, null, $field['groupId'], false, $itemId);

                        if (!empty($levelFields['listingtfieldgroup_id']) && is_array($levelFields['listingtfieldgroup_id'])) {
                            $showCheckList[$field['groupId']] = in_array($field['groupId'], $levelFields['listingtfieldgroup_id']) || in_array($field['groupId'], array_column($levelFields['listingtfieldgroup_id'], 'listingTFieldGroupId'));
                        } else {
                            $showCheckList[$field['groupId']] = false;
                        }

                        /** @var ListingTFieldGroup $checkboxGroup */
                        $checkboxGroups[$field['groupId']] = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTFieldGroup')->find($field['groupId']);
                    }
                }
            } elseif ($field['fieldType'] === 'text') {
                if (!empty($field['groupId'])) {
                    if (isset($textFieldBlock[$field['groupId']])) {
                        $textFieldBlock[$field['groupId']] .= $this->renderTextField($field, $listingField, $members);
                    } else {
                        $textFieldBlock[$field['groupId']] = $this->renderTextField($field, $listingField, $members);
                    }

                    if(empty($textFieldGroups[$field['groupId']] )) {
                        $textFieldGroupMessage[$field['groupId']] = $listingLevelFieldService->getBlockFieldListingLevelText(null, $members, null, $field['groupId'], false, $itemId);

                        if (!empty($levelFields['listingtfieldgroup_id']) && is_array($levelFields['listingtfieldgroup_id'])) {
                            $showTextFieldGroup[$field['groupId']] = in_array($field['groupId'], $levelFields['listingtfieldgroup_id']) || in_array($field['groupId'], array_column($levelFields['listingtfieldgroup_id'], 'listingTFieldGroupId'));
                        } else {
                            $showTextFieldGroup[$field['groupId']] = false;
                        }

                        /** @var ListingTFieldGroup $textFieldGroup */
                        $textFieldGroups[$field['groupId']] = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTFieldGroup')->find($field['groupId']);
                    }
                }
            } elseif ($field['fieldType'] === 'textarea') {
                $listingTemplateFieldBlock .= $this->renderTextAreaField($field, $listingField, null, $showField, $blockMessage);
            } elseif ($field['fieldType'] === 'link') {
                $listingTemplateFieldBlock .= $this->renderTextLinkField($field, $listingField, $showField, $blockMessage);
            } elseif ($field['fieldType'] === 'range') {
                $listingTemplateFieldBlock .= $this->renderRangeField($field, $listingField, $showField, $blockMessage);
            }
        }

        if(!empty($checkboxGroups)) {
            foreach($checkboxGroups as $groupId => $checkboxGroup) {
                if(!empty($checkboxBlock[$groupId])) {
                    $listingTemplateFieldBlock .= $this->renderCheckList($checkboxGroup, $checkboxBlock[$groupId], $showCheckList[$groupId], $checkListBlockMessage[$groupId]);
                }
            }
        }

        if(!empty($textFieldGroups)) {
            foreach($textFieldGroups as $groupId => $textFieldGroup) {
                if(!empty($textFieldBlock[$groupId])) {
                    $listingTemplateFieldBlock .= $this->renderTextFields($textFieldGroup, $textFieldBlock[$groupId], $showTextFieldGroup[$groupId], $textFieldGroupMessage[$groupId]);
                }
            }
        }

        return $listingTemplateFieldBlock;
    }

    /**
     * @param $field
     * @param $listingField
     * @param bool $showField
     * @param string $blockMessage
     * @return string
     */
    public function renderDropdownField($field, $listingField, $showField = true, $blockMessage = null)
    {
        $dropdownOptions = json_decode($field['attributes'], true)['dropdownOptions'];

        return $this->container->get('templating')->render('@Listing/listingForm/dropdown.html.twig', [
            'field'           => $field,
            'dropdownOptions' => $dropdownOptions,
            'listingField'    => $listingField,
            'showField'       => $showField,
            'blockMessage'    => $blockMessage
        ]);
    }

    /**
     * @param $field
     * @param $listingField
     * @return string
     */
    public function renderCheckboxField($field, $listingField)
    {
        return $this->container->get('templating')->render('@Listing/listingForm/checkbox.html.twig', [
            'field'        => $field,
            'listingField' => $listingField
        ]);
    }

    /**
     * @param ListingTFieldGroup $fieldTFieldGroup
     * @param $checkboxBlock
     * @param bool $showField
     * @param string $blockMessage
     * @return string
     */
    public function renderCheckList(ListingTFieldGroup $fieldTFieldGroup, $checkboxBlock, $showField = true, $blockMessage = null)
    {
        return $this->container->get('templating')->render('@Listing/listingForm/checklist.html.twig', [
            'fieldGroup'    => $fieldTFieldGroup,
            'checkboxBlock' => $checkboxBlock,
            'showField'     => $showField,
            'blockMessage'  => $blockMessage
        ]);
    }

    /**
     * @param $field
     * @param $listingField
     * @return string
     * @throws \Twig_Error
     */
    public function renderTextField($field, $listingField, $members = null)
    {
        return $this->container->get('templating')->render('@Listing/listingForm/text.html.twig', [
            'field'        => $field,
            'listingField' => $listingField,
            'members'      => $members
        ]);
    }

    /**
     * @param ListingTFieldGroup $fieldTFieldGroup
     * @param $textFieldBlock
     * @param bool $showField
     * @param string $blockMessage
     * @return string
     */
    public function renderTextFields(ListingTFieldGroup $fieldTFieldGroup, $textFieldBlock, $showField = true, $blockMessage = null)
    {
        return $this->container->get('templating')->render('@Listing/listingForm/textfields.html.twig', [
            'group'          => $fieldTFieldGroup,
            'textFieldBlock' => $textFieldBlock,
            'showField'      => $showField,
            'blockMessage'   => $blockMessage
        ]);
    }

    /**
     * @param $field
     * @param $listingField
     * @param $locale
     * @param bool $showField
     * @param string $blockMessage
     * @return string
     */
    public function renderTextAreaField($field, $listingField, $locale = null, $showField = true, $blockMessage = null)
    {
        $descriptionType = json_decode($field['attributes'], true)['descriptionType'];

        $msgCharsLeft = $this->container->get('translator')->trans('characters left', [], 'administrator', $locale);

        return $this->container->get('templating')->render('@Listing/listingForm/textarea.html.twig', [
            'field'           => $field,
            'descriptionType' => $descriptionType,
            'msgCharsLeft'    => $msgCharsLeft,
            'listingField'    => $listingField,
            'showField'       => $showField,
            'blockMessage'    => $blockMessage
        ]);
    }

    /**
     * @param $field
     * @param $listingField
     * @param bool $showField
     * @param string $blockMessage
     * @return string
     */
    public function renderTextLinkField($field, $listingField, $showField = true, $blockMessage = null)
    {
        return $this->container->get('templating')->render('@Listing/listingForm/textlink.html.twig', [
            'field'         => $field,
            'listingField'  => $listingField,
            'showField'     => $showField,
            'blockMessage'  => $blockMessage
        ]);
    }

    /**
     * @param $field
     * @param $listingField
     * @param bool $showField
     * @param string $blockMessage
     * @return string
     */
    public function renderRangeField($field, $listingField, $showField = true, $blockMessage = null)
    {
        $attributes = json_decode($field['attributes'], true);

        return $this->container->get('templating')->render('@Listing/listingForm/range.html.twig', [
            'field'         => $field,
            'attributes'    => $attributes,
            'listingField'  => $listingField,
            'showField'     => $showField,
            'blockMessage'  => $blockMessage
        ]);
    }

    /**
     * @return ListingTField[]
     */
    public function getLinkedListings($listingtemplate_id)
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->findBy([
             'fieldType'        => 'listing',
            'listingTemplateId' => $listingtemplate_id
        ]);
    }

    /**
     * @param ListingTemplate $listingTemplate
     * @return ListingTField[]
     */
    public function getCustomFieldsByTemplate(ListingTemplate $listingTemplate)
    {
        $listingCustomFields = $listingTemplate->getFields();

        $customFields = [];
        $customFieldGroups = [];

        if($listingCustomFields !== null) {
            $customFieldCriteria = Criteria::create();

            $customFieldCriteria->where(Criteria::expr()->neq('fieldType', 'default'));
            $customFieldCriteria->andWhere(Criteria::expr()->isNull('listingTFieldGroup'));

            $customFields = $listingCustomFields->matching($customFieldCriteria)->getValues();

            $customFieldGroups = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->getTemplateCustomFieldGroups($listingTemplate->getId());
        }

        return array_merge($customFields, $customFieldGroups);
    }

    /**
     * @return mixed
     */
    public function getAllCustomFields()
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->getAllCustomFields();
    }
}
