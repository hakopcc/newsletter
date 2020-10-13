<?php

namespace ArcaSolutions\ListingBundle\Twig\Extension;

use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingFieldValue;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Sample\ListingSample;
use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class BlocksExtension
 *
 * @package ArcaSolutions\ListingBundle\Twig\Extension
 */
class DetailExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('listingContent', [$this, 'listingContent']),
            new Twig_SimpleFunction('renderListingHeader', [$this, 'renderListingHeader'], [
                'needs_environment' => true,
                'debug'             => true,
                'is_safe'           => ['html'],
            ]),
            new Twig_SimpleFunction('renderListingWidgetBySection', [$this, 'renderListingWidgetBySection'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new Twig_SimpleFunction('getLinkedListings', [$this, 'getLinkedListings']),
        ];
    }

    /**
     * @param Listing $listing
     * @param ListingLevelFeatures $listingLevel
     * @param $reviewsPaginated
     * @param $address
     * @param $map
     * @param $listingWidgets
     * @return array
     */
    public function listingContent(Listing $listing, ListingLevelFeatures $listingLevel, $reviewsPaginated, $address, $map, $listingWidgets, $isSample = false)
    {
        $hasContent = false;

        $needValidation = [
            'review',
            'deals',
            'classifieds',
            'locations'
        ];

        $reviews_active = $this->container->get('doctrine')->getRepository('WebBundle:Setting')->getSetting('review_listing_enabled');

        if(!empty($listingWidgets['listingWidgets'])) {
            $criteria = Criteria::create();

            $criteria->where(Criteria::expr()->eq('level', $listing->getLevel()));

            foreach ($listingWidgets['listingWidgets']['tabs'] as $tab => $listingTabSection) {
                $tabContent = false;

                foreach ($listingTabSection as $sectionWidgets) {
                    /** @var ListingTemplateListingWidget $listingWidget */
                    foreach ($sectionWidgets as $listingWidget) {
                        if ($tabContent === false) {
                            /** @var ListingTField $field */
                            foreach ($listingWidget->getListingTFields()->getValues() as $field) {
                                $fieldGroup = $field->getListingTFieldGroup();
                                $levelField = null;

                                if($fieldGroup) {
                                    if($fieldGroup->getLevels() !== null) {
                                        $levelField = $fieldGroup->getLevels()->matching($criteria)->getValues();
                                    }
                                } elseif ($field->getLevels() !== null) {
                                    $levelField = $field->getLevels()->matching($criteria)->getValues();
                                }

                                if (!empty($levelField) && ($levelField[0]->getQuantity() === null || $levelField[0]->getQuantity() > 0)) {
                                    $fieldName = $levelField[0]->getField();
                                    $getField = 'get' . ucfirst($fieldName);

                                    if ($fieldName === 'custom') {
                                        $fieldId = $field->getId();

                                        /** @var ListingFieldValue[] $fieldValue */
                                        $fieldValues = $this->container->get('doctrine')->getRepository('ListingBundle:ListingFieldValue')->findBy([
                                            'listingTFieldId' => $fieldId,
                                            'listingId' => $listing->getId()
                                        ]);

                                        if($isSample) {
                                            $tabContent = true;
                                            $hasContent = true;
                                        }

                                        if (!empty($fieldValues)) {
                                            /** @var ListingFieldValue $fieldValue */
                                            foreach($fieldValues as $fieldValue) {
                                                if(!empty($fieldValue->getValue())) {
                                                    $tabContent = true;
                                                    $hasContent = true;
                                                }
                                            }
                                        }
                                    } else {
                                        if (!in_array($fieldName, $needValidation, true)) {
                                            if (!empty($listing->$getField())) {
                                                $tabContent = true;
                                                $hasContent = true;
                                            }
                                        } elseif ($fieldName === 'deals' || $fieldName === 'classifieds') {
                                            if (!empty($listing->$getField()->getValues())) {
                                                $tabContent = true;
                                                $hasContent = true;
                                            }
                                        } elseif ($fieldName === 'review') {
                                            if ($reviews_active && !empty($reviewsPaginated['reviews']->count())) {
                                                $tabContent = true;
                                                $hasContent = true;
                                            }
                                        } elseif ($fieldName === 'locations') {
                                            if (!empty($map) || !empty($address) || !empty($listing->$getField())) {
                                                $tabContent = true;
                                                $hasContent = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                /* ModStores Hooks */
                HookFire("detailextension_before_settabhascontent", [
                    'has_content'               => &$tabContent,
                    'tab'                       => &$listingWidgets['listingTabs'][$tab],
                    'tab_section_widgets'       => &$listingTabSection,
                    'listing'                   => &$listing,
                    'listing_level_features'    => $listingLevel
                ]);

                $listingWidgets['listingTabs'][$tab]->setHasContent($tabContent);
            }
        }

        return [
            'hasContent'     => $hasContent,
            'listingWidgets' => $listingWidgets
        ];
    }


    /**
     * @param Twig_Environment $twig_Environment
     * @param ListingTemplateListingWidget $header
     * @return string
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function renderListingHeader(Twig_Environment $twig_Environment, ListingTemplateListingWidget $header)
    {
        $return = '';

        $headerWidgetFile = '::widgets/detail-editor/header/' . $header->getListingWidget()->getTwigFile();

        if ($twig_Environment->getLoader()->exists($headerWidgetFile)) {

            $content = json_decode($header->getContent());

            $data = [
                'content'    => $content,
            ];

            $renderedHtml = $twig_Environment->render($headerWidgetFile, $data);

            $return .= $renderedHtml;
        } else {
            $this->container->get('logger')->addError('Twig file not found: ' . $header->getListingWidget()->getTwigFile());
        }

        return $return;
    }

    /**
     * @param Twig_Environment $twig_Environment
     * @param string $section
     * @param array $listingWidgets
     * @param Listing $listing
     * @param bool $isSample
     * @return string
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     * @throws \Exception
     */
    public function renderListingWidgetBySection(Twig_Environment $twig_Environment, string $section, array $listingWidgets, Listing $listing, $isSample = false)
    {
        $return = '';
        $previousContent = '';

        /** @var ListingTemplateListingWidget[] $listingWidgets */
        foreach($listingWidgets as $listingWidget) {
            $listingWidgetFile = '::widgets/detail-editor' . $listingWidget->getListingWidget()->getTwigFile();
            $listingWidgetSectionFile = '::widgets/detail-editor/' . $section . $listingWidget->getListingWidget()->getTwigFile();

            if ($twig_Environment->getLoader()->exists($listingWidgetSectionFile)) {
                $listingWidgetFile = $listingWidgetSectionFile;
            }

            /* ModStores Hooks */
            HookFire("detailextension_after_setlistingwidgettwigname", [
                'listingtemplate_listingwidget' => &$listingWidget,
                'listingwidget_section'         => &$section,
                'listingwidget_section_twigname'=> &$listingWidgetSectionFile,
                'listingwidget_twigname'        => &$listingWidgetFile
            ]);

            if ($twig_Environment->getLoader()->exists($listingWidgetFile)) {

                $content = json_decode($listingWidget->getContent());

                $data = [
                    'content'    => $content,
                ];

                $fieldsValue = $listing->getFieldsValue();
                if($fieldsValue !== null || $isSample) {
                    $listingFields = [];
                    $listingFieldGroups = [];
                    $listingFieldIds = [];
                    $attributes = [];

                    if (!empty($listingWidget->getListingTFieldGroups()->getValues())) {
                        foreach ($listingWidget->getListingTFieldGroups()->getValues() as $fieldGroup) {
                            $listingFieldGroups[] = $fieldGroup;
                            /** @var ListingTField $listingTField */
                            foreach ($fieldGroup->getListingTFields()->getValues() as $listingTField) {
                                $listingFields[] = $listingTField;
                                $listingFieldIds[] = $listingTField->getId();
                                $attributes[] = $listingTField->getAttributes();
                            }
                        }
                    } elseif (!empty($listingWidget->getListingTFields()->getValues())) {
                        /** @var ListingTField $listingTField */
                        foreach ($listingWidget->getListingTFields()->getValues() as $listingTField) {
                            $listingFields[] = $listingTField;
                            $listingFieldIds[] = $listingTField->getId();
                            $attributes[] = $listingTField->getAttributes();
                        }
                    }

                    $values = [];

                    if(!$isSample) {
                        $fieldsValue = $listing->getFieldsValue();
                        $fieldCriteria = Criteria::create();
                        $fieldCriteria->where(Criteria::expr()->in('listingTField', $listingFieldIds));
                        $values = $fieldsValue->matching($fieldCriteria);
                    } else {
                        /** @var ListingTField $listingField */
                        foreach($listingFields as $listingField) {
                            if ($listingField->getFieldType() === 'text') {
                                $values[]  = [
                                    'label' => $this->container->get('translator')->trans('Details'),
                                    'value' => 'Lorem ipsum'
                                ];
                            } elseif ($listingField->getFieldType() === 'checkbox') {
                                $values[] = $listingField->getLabel();
                            } elseif ($listingField->getFieldType() === 'dropdown') {
                                $dropdownOptions = json_decode($listingField->getAttributes(), true);

                                if(!empty($dropdownOptions['dropdownOptions'])) {
                                    $randomDropdownOption = array_rand($dropdownOptions['dropdownOptions'], 1);

                                    $values[] = [
                                        'label'  => $listingField->getLabel(),
                                        'value'  => $dropdownOptions['dropdownOptions'][$randomDropdownOption]['value']
                                    ];
                                }
                            } elseif ($listingField->getFieldType() === 'textarea') {
                                $values[] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque luctus enim ac diam malesuada vestibulum vitae at tortor. Nullam nec porttitor arcu.' .
                                    'Pellentesque laoreet lorem egestas felis lobortis eu tincidunt nulla tempor. Phasellus adipiscing fringilla tempus. Class aptent taciti sociosqu ad litora torquent per conubia nostra,' .
                                    'per inceptos himenaeos. Curabitur sed sapien ut eros porta volutpat et quis leo. Aenean tincidunt ipsum quis nisl blandit nec placerat eros consectetur.';
                            } elseif ($listingField->getFieldType() === 'range') {
                                $ranges = json_decode($listingField->getAttributes(), true);

                                if(isset($ranges['minRange'], $ranges['maxRange'])) {
                                    $values[] = random_int($ranges['minRange'], $ranges['maxRange']);
                                }
                            } elseif ($listingField->getFieldType() === 'link') {
                                $values[] = 'javascript:void(0);';
                            }
                        }
                    }

                    $data['values'] = $values;
                    $data['listingFields'] = $listingFields;
                    $data['listingFieldGroups'] = $listingFieldGroups;

                    if($section){
                        $data['section'] = $section;
                    }

                    if(!empty(array_filter($attributes))) {
                        $data['attributes'] = json_decode($attributes[0], true);
                    }
                }

                $renderedHtml = $twig_Environment->render($listingWidgetFile, $data);

                if ($listingWidget->getListingWidget()->getTitle() === 'Separator' && !$previousContent) {
                    $renderedHtml = '';
                }
                $return .= $renderedHtml;

                $previousContent = $renderedHtml;

            } else {
                $this->container->get('logger')->addError('Twig file not found: ' . $listingWidget->getListingWidget()->getTwigFile());
            }
        }

        return $return;
    }

    /**
     * @param Listing $listing
     * @param $listingFields
     * @param $isSample
     * @return array
     */
    public function getLinkedListings(Listing $listing, $listingFields, $isSample, $level)
    {
        if(empty($listingFields)) {
            return [];
        }

        $listings = [];

        $linkedListings = $listing->getLinkedListings();

        if(!$isSample) {
            if (!empty($linkedListings)) {
                $fieldCriteria = Criteria::create();
                $fieldCriteria->where(Criteria::expr()->in('field', $listingFields));

                foreach ($linkedListings->matching($fieldCriteria)->getValues() as $linkedListings) {
                    $listings[] = $linkedListings->getLinkedListing();
                }
            }
        } else {
            foreach($listingFields as $listingField) {
                if(!empty($level->customFields['linkedListings'][$listingField->getId()])) {
                    $quantity = $level->customFields['linkedListings'][$listingField->getId()];
                    $listings = new ArrayCollection();

                    for ($i = 0; $i < $quantity; $i++) {
                        $listings->add(new ListingSample($listing->getLevel(), $this->container->get('translator'), $this->container->get('doctrine'), $listing->getListingTemplate()->getId()));
                    }
                }
            }
        }

        return $listings;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'detail_listing';
    }
}
