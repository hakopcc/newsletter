<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\ListingBundle\Entity\ListingLevelField;
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup;
use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use http\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class ListingTemplateListingWidgetService
 * @package ArcaSolutions\WysiwygBundle\Services
 */
class ListingTemplateListingWidgetService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $defaultFields = [
        ListingWidget::ABOUT => [
            'fields'   => [
                ListingTField::DESCRIPTION,
                ListingTField::LONG_DESCRIPTION
            ]
        ],
        ListingWidget::FEATURES => [
            'fields'   => [
                ListingTField::FEATURES
            ]
        ],
        ListingWidget::ADDITIONAL_INFORMATION => [
            'fields'   => [
                ListingTField::ATTACHMENT_FILE
            ]
        ],
        ListingWidget::VIDEO => [
            'fields'   => [
                ListingTField::VIDEO
            ]
        ],
        ListingWidget::RECENT_REVIEWS => [
            'fields'   => [
                ListingTField::REVIEW
            ]
        ],
        ListingWidget::HOURS => [
            'fields'   => [
                ListingTField::HOURS_WORK
            ]
        ],
        ListingWidget::LOCATION => [
            'fields'   => [
                ListingTField::LOCATIONS
            ]
        ],
        ListingWidget::SOCIAL_BUTTONS => [
            'fields'   => [
                ListingTField::SOCIAL_NETWORK
            ]
        ],
        ListingWidget::FACEBOOK_FEED => [
            'fields'   => [
                ListingTField::SOCIAL_NETWORK
            ]
        ],
        ListingWidget::PHOTO_GALLERY => [
            'fields'   => [
                ListingTField::IMAGES
            ]
        ],
        ListingWidget::REVIEWS_PAGINATED => [
            'fields'   => [
                ListingTField::REVIEW
            ]
        ],
        ListingWidget::ASSOCIATED_DEALS => [
            'fields'   => [
                ListingTField::DEALS
            ]
        ],
        ListingWidget::ASSOCIATED_CLASSIFIEDS => [
            'fields'   => [
                ListingTField::CLASSIFIEDS
            ]
        ]
    ];

    /**
     * ListingTemplateListingWidgetService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $listingTemplateId
     * @param array $postArray
     *
     * @return array
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws MappingException
     * @internal param array $pageWidgets
     */
    public function saveListingTemplateListingWidgets($listingTemplateId, array $postArray)
    {
        $translator = $this->container->get('translator');
        $doctrine = $this->container->get('doctrine');
        /** @var EntityManager $em */
        $em = $doctrine->getManager();

        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        $options = [];
        $counterWidgets = 1;
        $counterCategories =1;
        $counterTabs = 1;
        if(!empty($listingTemplateId)) {

            /** @var ListingTemplate $listingTemplate */
            $listingTemplate = $doctrine->getRepository('ListingBundle:ListingTemplate')->find($listingTemplateId);

            /* Success message */
            $return = [
                'success' => true,
                'message' => $translator->trans('Changes successfully saved.', [], 'messages', $sitemgrLanguage),
            ];

            $listingTemplateTabArray = json_decode($postArray['serializedTabs'], true);

            if(!empty($listingTemplateTabArray)) {
                foreach ($listingTemplateTabArray as $order => $listingTab) {

                    if(!empty($listingTab['tabId'])) {
                        $listingTemplateTab = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->find($listingTab['tabId']);

                        if(empty($listingTemplateTab)) {
                            $listingTemplateTab = new ListingTemplateTab($listingTab['tabId']);
                            $listingTemplateTab->setListingTemplate($listingTemplate);
                        }

                        $listingTemplateTab->setTitle($listingTab['tabTitle']);
                        $listingTemplateTab->setOrder($order);
                        $em->persist($listingTemplateTab);
                        $options['Tabs '.$counterTabs++] = $listingTemplateTab->getTitle();
                        unset($listingTemplateTab);
                    }
                }

                $metadata = $em->getClassMetaData(ListingTemplateTab::class);
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_IDENTITY);
            }

            //widgets header
            $headerWidgets = json_decode($postArray['serializedHeader'], true);
            if(!empty($headerWidgets)) {
                foreach($headerWidgets as $order => $headerWidget) {
                    if(!empty($headerWidget)) {
                        if (!empty($headerWidget['listingTemplateListingWidgetIdInput'])) {
                            /** @var ListingTemplateListingWidget $listingTemplateListingWidget */
                            $listingTemplateListingWidget = $doctrine->getRepository('WysiwygBundle:ListingTemplateListingWidget')->find($headerWidget['listingTemplateListingWidgetIdInput']);
                            $listingTemplateListingWidget->setOrder($order+1);
                            $options['Widgets ' .$counterWidgets++] = $listingTemplateListingWidget->getListingWidget()->getTitle();
                        } else {
                            /** @var ListingWidget $listingWidget */
                            $listingWidget = $doctrine->getRepository('WysiwygBundle:ListingWidget')->find($headerWidget['listingWidgetId']);
                            $options['Widgets ' .$counterWidgets++] = $listingWidget->getTitle();
                            $listingTemplateListingWidget = $this->saveListingWidget(null, $listingTemplate, $listingWidget, null, $order+1);
                            if (empty($listingTemplateListingWidget)) {
                                $return['success'] = false;
                                $return['message'] = $translator->trans('Something went wrong!', [], 'widgets',
                                    $sitemgrLanguage);
                            }
                        }
                    }
                }
                $em->flush();
            }

            // Decode array containing each widget information
            $listingTemplateListingWidgets = json_decode($postArray['serializedPost'], true);

            //priceAdditional
            if ($postArray['statusPricing'] === 'disabled') {
                $listingTemplate->setPrice((!empty($postArray['priceAdditional'])?$postArray['priceAdditional']:0));
                $disabledPrice = 'no';
                $additionPrice = 'yes';
            } else {
                $listingTemplate->setPrice(0);
                $additionPrice = 'no';
                $disabledPrice = 'yes';
            }

            $listingTemplate->setTemplateFree($postArray['statusPricing']);

            //Set Listing Type Information
            if (!empty($postArray['title'])) {
                $listingTemplate->setTitle($postArray['title']);
            }

            if ($postArray['listingTemplateState'] === 'enabled') {
                $listingTemplate->setStatus('enabled');
            } else {
                $listingTemplate->setStatus('disabled');
            }

            if(!empty($postArray['summaryItem'])) {
                $listingTemplate->setSummaryTemplate($postArray['summaryItem']);
            }

            $listingTemplate->setUpdated(new \DateTime());

            if(!empty($postArray['return_categories'])) {
                $categories = explode(',', $postArray['return_categories']);

                foreach($categories as $category) {
                    $listingCategory = $this->container->get('doctrine')->getRepository('ListingBundle:ListingCategory')->find($category);
                    if(!empty($listingCategory)) {
                        $listingCategoryArray[] = $listingCategory;
                        $options['Category ' .$counterCategories++] = $listingCategory->getTitle();
                    }
                }

                if(!empty($listingCategoryArray)) {
                    $listingTemplate->setCategories($listingCategoryArray);
                }

            } else {
                $options['Category'] = 'All';
                $listingTemplate->setCategories(null);
            }

            if ($listingTemplateListingWidgets) {
                foreach ($listingTemplateListingWidgets as $order => $item) {
                    if (!empty($item['listingTemplateListingWidgetIdInput'])) {
                        /** @var ListingTemplateListingWidget $listingTemplateListingWidget */
                        $listingTemplateListingWidget = $doctrine->getRepository('WysiwygBundle:ListingTemplateListingWidget')->find($item['listingTemplateListingWidgetIdInput']);

                        if ($listingTemplateListingWidget !== null) {
                            $listingTemplateListingWidget->setOrder($order);
                            $options['Widget ' . $counterWidgets++] = $listingTemplateListingWidget->getListingWidget()->getTitle();
                        }
                    } else {
                        /** @var ListingWidget $listingWidget */
                        $listingWidget = $doctrine->getRepository('WysiwygBundle:ListingWidget')->find($item['listingWidgetId']);
                        /** @var ListingTemplateTab $listingTemplateTab */
                        $listingTemplateTab = $doctrine->getRepository('ListingBundle:ListingTemplateTab')->find($item['listingTemplateTabId']);

                        $listingTemplateListingWidget = $this->saveListingWidget(null, $listingTemplate, $listingWidget, $listingTemplateTab, $order+1);

                        if ($listingTemplateListingWidget) {
                            $options['Widget '.$counterWidgets++] = $listingTemplateListingWidget->getListingWidget()->getTitle();
                        } else {
                            $return['success'] = false;
                            $return['message'] = $translator->trans('Something went wrong!', [], 'widgets',
                                $sitemgrLanguage);
                        }
                    }
                }
                $em->flush();
                $em->clear();
            }


            $options ['Name'] = $listingTemplate->getTitle();
            $options ['Additional price'] =$additionPrice;
            $options ['Disabled price'] =$disabledPrice;
            $options ['Summary option'] = $listingTemplate->getSummaryTemplate();
            if($counterWidgets>1){
                $options ['Total of widgets'] =$counterWidgets-1;
            }else{
                $options ['Total of widgets'] =$counterWidgets;
            }
            $this->container->get('mixpanel.helper')->trackEvent('Edited an existing listing template', $options);

        } else {
            $return = [
                'success' => false,
                'message' => $translator->trans('The server encountered an internal error or misconfiguration and was unable to complete your request.', [], 'messages', $sitemgrLanguage),
            ];
        }

        /* ModStores Hooks */
        HookFire('wysiwyg_listingtemplatelistingwidgetservice_after_save', [
            'http_post_array' => $postArray,
            'listing_template_id' => $listingTemplateId,
            'successfully_saved' => !empty($return) && array_key_exists('success',$return) && $return['success'] === true
        ]);

        return $return;
    }

    /**
     * Save a changed content of a widget
     *
     * @param integer $id
     * @param string $content
     * @param $attributes
     * @return ListingTemplateListingWidget|null|object
     */
    public function saveWidgetContent($id, $content, $attributes)
    {
        // Save Widget customized content (Page_Widget Table)
        try {
            $em = $this->container->get('doctrine')->getManager();
            if ($id) {
                /** @var ListingTemplateListingWidget $listingTemplateListingWidget */
                $listingTemplateListingWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingTemplateListingWidget')->find($id);
                $listingTemplateListingWidget->setContent($content);

                $em->persist($listingTemplateListingWidget);

                $contentArray = json_decode($content, true);

                $listingTFields = $listingTemplateListingWidget->getListingTFields()->getValues();
                if(!empty($attributes) && !empty($listingTFields)) {
                    /** @var ListingTField $field */
                    foreach($listingTFields as $field) {
                        $currentAttributes = json_decode($field->getAttributes(), true);

                        if(!empty($currentAttributes['dropdownOptions'])) {
                            $newAttributes = json_decode($attributes, true);

                            foreach ($currentAttributes['dropdownOptions'] as $currentOptions) {
                                if(!in_array($currentOptions['value'], array_column($newAttributes['dropdownOptions'], 'value'), true)) {
                                    $listingValues = $this->container->get('doctrine')->getRepository('ListingBundle:ListingFieldValue')->findBy([
                                        'value'         => $currentOptions['value'],
                                        'listingTField' => $field
                                    ]);

                                    if(!empty($listingValues)) {
                                        foreach($listingValues as $listingValue) {
                                            $em->remove($listingValue);
                                        }

                                        $em->flush();
                                    }
                                }
                            }
                        }

                        $field->setAttributes($attributes);
                        $em->persist($field);
                    }

                    $em->flush();
                }

                if(!empty($contentArray['fieldTitle']) && !empty($listingTFields)) {
                    /** @var ListingTFieldGroup $listingTFieldGroup */
                    if(!empty($listingTFields)) {
                        $listingTFieldGroup = $listingTFields[0]->getListingTFieldGroup();
                    } else {
                        $listingTFieldGroup = null;
                    }

                    if($listingTFieldGroup !== null) {
                        $listingTFieldGroup->setLabel($contentArray['fieldTitle']);
                        $em->persist($listingTFieldGroup);
                        $em->flush($listingTFieldGroup);
                        if(!empty($contentArray['groupFields'])) {
                            foreach($listingTFieldGroup->getListingTFields()->getValues() as $listingTField) {
                                $fieldUpdated = false;
                                $fieldType = $listingTField->getFieldType();

                                foreach($contentArray['groupFields'] as $key => $field) {
                                    if(!$fieldUpdated && $listingTField->getLabel() === $field['title']) {
                                        if ($contentArray['required'] !== 'disabled') {
                                            $listingTField->setRequired(true);
                                        } else {
                                            $listingTField->setRequired(false);
                                        }
                                        unset($contentArray['groupFields'][$key]);
                                        $em->persist($listingTField);
                                        $fieldUpdated = true;
                                    }
                                }

                                if(!$fieldUpdated) {
                                    $listingTemplateListingWidget->removeListingTField($listingTField);
                                    $em->remove($listingTField);
                                }
                            }

                            $em->flush();

                            if(!empty($fieldType) && !empty($contentArray['groupFields'])) {
                                foreach ($contentArray['groupFields'] as $field) {
                                    $listingTemplateField = new ListingTField();
                                    if ($contentArray['required'] !== 'disabled') {
                                        $listingTemplateField->setRequired(true);
                                    } else {
                                        $listingTemplateField->setRequired(false);
                                    }
                                    $listingTemplateField->setLabel($field['title']);
                                    $listingTemplateField->setPlaceholder($field['placeholder']);
                                    $listingTemplateField->setFieldType($fieldType);
                                    $listingTemplateField->setListingTFieldGroup($listingTFieldGroup);
                                    $listingTemplateField->setListingTemplate($listingTemplateListingWidget->getListingTemplate());
                                    $listingTemplateField->addListingWidget($listingTemplateListingWidget);
                                    $listingTemplateListingWidget->addListingTField($listingTemplateField);
                                    $em->persist($listingTemplateField);
                                }

                                $em->flush();
                            }
                        }
                    } else {
                        /** @var ListingTField $listingTField */
                        foreach($listingTFields as $listingTField) {
                            $listingTField->setLabel($contentArray['fieldTitle']);
                            if($contentArray['required'] !== 'disabled') {
                                $listingTField->setRequired(true);
                            } else {
                                $listingTField->setRequired(false);
                            }
                            if(!empty($contentArray['placeholder'])){
                                $listingTField->setPlaceholder($contentArray['placeholder']);
                            }
                            $em->persist($listingTField);
                        }
                        $em->flush();
                    }
                }

                return $listingTemplateListingWidget;
            }
        } catch (Exception $e) {
            $this->container->get('logger')->error($e->getMessage());
        }
    }

    /**
     * Reset all widgets of the Listing Type to the default configuration
     *
     * @param $listingTemplateId
     * @return array
     */
    public function resetListingTemplate($listingTemplateId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $translator = $this->container->get('translator');

        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);

        /* Success message */
        $return = [
            'success' => true,
            'message' => $translator->trans('Listing Template successfully reset.', [], 'messages', $sitemgrLanguage),
        ];

        try {
            $listingTemplateTabs = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->findBy([
                'listingTemplateId'  => $listingTemplateId,
            ]);

            if ($listingTemplateTabs) {
                foreach ($listingTemplateTabs as $listingTemplateTab) {
                    $em->remove($listingTemplateTab);
                }

                $em->flush();
            }

            $listingTemplateWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingTemplateListingWidget')->findBy([
                'listingTemplateId'  => $listingTemplateId,
            ]);

            if ($listingTemplateWidgets) {
                foreach ($listingTemplateWidgets as $listingTemplateWidget) {
                    $em->remove($listingTemplateWidget);
                }

                $em->flush();
            }

            $listingTFields = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->getTemplateCustomFields($listingTemplateId, 'object');

            if(!empty($listingTFields)) {
                /** @var ListingTField $field */
                foreach($listingTFields as $field) {
                    if($field->getFieldType() !== 'default') {
                        $em->remove($field);
                    }
                }

                $em->flush();
            }

            /** @var ListingTemplate $listingTemplate */
            $listingTemplate = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($listingTemplateId);

            $listingTemplateTitle = $listingTemplate->getTitle();

            /* Get Default Widgets Method */
            $method = 'get'.str_replace(' ', '', $listingTemplateTitle).'DefaultWidgets';

            if (method_exists($this, $method)) {
                $listingTemplateWidgetsArr = $this->$method();
            } else if (isset($this->$method)) {
                $listingTemplateWidgetsArr = call_user_func($this->$method);
            } else {
                $listingTemplateWidgetsArr[] = $this->getListingDefaultListingWidgets();
            }

            if (!empty($listingTemplateWidgetsArr)) {

                if(!empty(current($listingTemplateWidgetsArr)['header'])) {
                    $listingTemplateWidgetTitle = current($listingTemplateWidgetsArr)['header'];
                    $content = null;
                    if (is_array($listingTemplateWidgetTitle)) {
                        $content = json_encode(current($listingTemplateWidgetTitle)['content']);
                        $listingTemplateWidgetTitle = key($listingTemplateWidgetTitle);
                    }

                    /* @var $listingWidget ListingWidget */
                    $listingWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findOneBy([
                        'title'   => $listingTemplateWidgetTitle,
                        'section' => 'header'
                    ]);

                    $this->saveListingWidget(
                        $content,
                        $listingTemplate,
                        $listingWidget,
                        null
                    );
                }

                $order = 0;

                foreach (current($listingTemplateWidgetsArr)['tabs'] as $tab => $listingTemplateTabSections) {
                    $listingTemplateTab = new ListingTemplateTab();
                    $listingTemplateTab->setListingTemplate($listingTemplate);
                    $listingTemplateTab->setTitle($tab);
                    $listingTemplateTab->setOrder($order);
                    $em->persist($listingTemplateTab);
                    $em->flush();

                    foreach ($listingTemplateTabSections as $section => $listingTemplateSectionWidgets) {
                        foreach ($listingTemplateSectionWidgets as $listingTemplateWidgetTitle) {
                            $content = null;
                            if (is_array($listingTemplateWidgetTitle)) {
                                $content = json_encode(current($listingTemplateWidgetTitle)['content']);
                                $listingTemplateWidgetTitle = key($listingTemplateWidgetTitle);
                            }

                            /* @var $listingWidget ListingWidget */
                            $listingWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findOneBy([
                                'title' => $listingTemplateWidgetTitle,
                                'section' => $section
                            ]);

                            $this->saveListingWidget(
                                $content,
                                $listingTemplate,
                                $listingWidget,
                                $listingTemplateTab
                            );
                        }
                    }

                    $order++;
                }
            }
        } catch (Exception $e) {
            $return = ['success' => false, 'message' => $e->getMessage()];
        }

        return $return;
    }

    /**
     * Create a New widget for a page at the bottom
     *
     * @param $content
     *
     * @param ListingTemplate $listingTemplate
     * @param ListingWidget $listingWidget
     * @param ListingTemplateTab $listingTemplateTab
     * @param null $order
     * @param null $fieldType
     * @param null $attributes
     * @return ListingTemplateListingWidget|bool
     */
    public function saveListingWidget($content, ListingTemplate $listingTemplate, ListingWidget $listingWidget, ListingTemplateTab $listingTemplateTab = null, $order = null, $fieldType = null, $attributes = null)
    {
        try {
            $em = $this->container->get('doctrine')->getManager();

            $listingLevels = $this->container->get('listinglevel.service')->getAllListingLevels();

            // Get new widget Order
            if(empty($order)) {
                $order = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingTemplateListingWidget')->findLastOrder($listingTemplate->getId());
            }

            $listingTemplateListingWidget = new ListingTemplateListingWidget();
            $listingTemplateListingWidget->setContent($content ?: $listingWidget->getContent());
            $listingTemplateListingWidget->setListingTemplate($listingTemplate);
            $listingTemplateListingWidget->setListingWidget($listingWidget);
            $listingTemplateListingWidget->setListingTemplateTab($listingTemplateTab);
            $listingTemplateListingWidget->setOrder($order);

            $contentArray = json_decode($content, true);

            if(!empty($fieldType)) {
                if (!empty($contentArray['groupFields'])) {
                    $listingTFieldGroup = new ListingTFieldGroup();
                    $listingTFieldGroup->setLabel($contentArray['fieldTitle']);
                    $listingTFieldGroup->setListingTemplateListingWidget($listingTemplateListingWidget);

                    if($fieldType === 'checklist') {
                        $groupFieldType = 'checkbox';
                    } else {
                        $groupFieldType = 'text';
                    }

                    $listingTFields = [];
                    foreach ($contentArray['groupFields'] as $field) {
                        $listingTField = new ListingTField();
                        $listingTField->setLabel($field['title']);
                        $listingTField->setFieldType($groupFieldType);
                        $listingTField->setPlaceholder($field['placeholder']);
                        $listingTField->setListingTemplate($listingTemplate);
                        $listingTField->setListingTFieldGroup($listingTFieldGroup);
                        if(!empty($attributes)) {
                            $listingTField->setAttributes($attributes);
                        }

                        if($contentArray['required'] !== 'disabled') {
                            $listingTField->setRequired(true);
                        } else {
                            $listingTField->setRequired(false);
                        }
                        $listingTemplateListingWidget->addListingTField($listingTField);

                        $listingTField->addListingWidget($listingTemplateListingWidget);
                        $em->persist($listingTField);

                        $listingTFields[] = $listingTField;
                    }

                    $listingTFieldGroup->setListingTFields($listingTFields);

                    foreach($listingLevels as $level) {
                        $listingLevelField = new ListingLevelField();

                        $listingLevelField->setListingTFieldGroup($listingTFieldGroup);
                        $listingLevelField->setListingLevel($level);
                        $listingLevelField->setField('custom');

                        $em->persist($listingLevelField);
                    }

                    $em->persist($listingTFieldGroup);
                } elseif (!empty($contentArray['fieldTitle'])) {
                    $listingTField = new ListingTField();
                    $listingTField->setLabel($contentArray['fieldTitle']);
                    $listingTField->setFieldType($fieldType);
                    $listingTField->setListingTemplate($listingTemplate);
                    if(!empty($attributes)) {
                        $listingTField->setAttributes($attributes);
                    }

                    if($contentArray['required'] !== 'disabled') {
                        $listingTField->setRequired(true);
                    } else {
                        $listingTField->setRequired(false);
                    }
                    $listingTemplateListingWidget->addListingTField($listingTField);

                    $listingTField->addListingWidget($listingTemplateListingWidget);

                    foreach($listingLevels as $level) {
                        $listingLevelField = new ListingLevelField();

                        $listingLevelField->setListingTField($listingTField);
                        $listingLevelField->setListingLevel($level);
                        $listingLevelField->setField('custom');

                        if($listingTField->getFieldType() === 'listing') {
                            $listingLevelField->setQuantity(10);
                        }

                        $em->persist($listingLevelField);
                    }

                    $em->persist($listingTField);
                }
            } elseif(!empty($this->defaultFields[$listingWidget->getTitle()])) {
                foreach ($this->defaultFields[$listingWidget->getTitle()]['fields'] as $field) {
                    /** @var ListingTField $listingTField */
                    $listingTField = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->findOneBy([
                        'label' => $field
                    ]);

                    if ($listingTField !== null) {
                        $listingTemplateListingWidget->addListingTField($listingTField);

                        $em->persist($listingTField);
                    }
                }
            }

            $em->persist($listingTemplateListingWidget);
            $em->flush();

            return $listingTemplateListingWidget;
        } catch (Exception $e) {
            $this->container->get('logger')->error($e->getMessage());

            return false;
        }
    }

    /**
     * @param $listingTemplateListingWidgetId
     * @return bool
     */
    public function deleteListingWidgetFromListingTemplate($listingTemplateListingWidgetId)
    {
        $container = $this->container;
        $em = $container->get('doctrine')->getManager();
        $return = false;

        /** @var ListingTemplateListingWidget $listingTemplateListingWidget */
        $listingTemplateListingWidget = $container->get('doctrine')->getRepository('WysiwygBundle:ListingTemplateListingWidget')->find($listingTemplateListingWidgetId);
        if ($listingTemplateListingWidget) {
            if(!empty($listingTemplateListingWidget->getListingTFields()->getValues())) {
                $listingTFields = $listingTemplateListingWidget->getListingTFields()->getValues();

                /** @var ListingTField $field */
                foreach($listingTFields as $field) {
                    if($field->getFieldType() !== 'default') {
                        $em->remove($field);
                    }
                }
                if(!empty($listingTFields)) {
                    $listingTFieldGroup = $listingTFields[0]->getListingTFieldGroup();
                } else {
                    $listingTFieldGroup = null;
                }
                if($listingTFieldGroup !== null) {
                    $em->remove($listingTFieldGroup);
                }
            }

            $em->remove($listingTemplateListingWidget);
            $em->flush();
            $return = true;
        }

        return $return;
    }

    /**
     * @param int $listingTemplateId
     * @return bool
     */
    public function checkIfTemplateContainsHeader(int $listingTemplateId)
    {
        $headerWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findOneBy([
            'title' => ListingWidget::HEADER
        ]);

        $headerTemplateWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingTemplateListingWidget')->findBy([
            'listingWidgetId'   => $headerWidget->getId(),
            'listingTemplateId' => $listingTemplateId
        ]);

        return !empty($headerTemplateWidgets);
    }

    /**
     * Return standard Listing Widgets
     *
     * @return array
     */
    public function getListingDefaultWidgets()
    {
        $listingTemplateListingWidgets = [];

        $listingTemplateListingWidgets[ListingTemplate::LISTING] = $this->getListingDefaultListingWidgets();

        return $listingTemplateListingWidgets;
    }

    /**
     * Return standard detail main Listing Widgets
     *
     * @return array
     */
    public function getListingDefaultListingWidgets()
    {
        $standardWidgets = [
            ListingWidget::HEADER_SECTION => ListingWidget::HEADER,
            'tabs' => [
                ListingTemplateTab::OVERVIEW => [
                    ListingWidget::MAIN_SECTION => [
                        '1' => [
                            ListingWidget::ABOUT => [
                                'fields'   => [
                                    ListingTField::DESCRIPTION,
                                    ListingTField::LONG_DESCRIPTION
                                ]
                            ]
                        ],
                        '2' => ListingWidget::SEPARATOR,
                        '3' => [
                            ListingWidget::FEATURES => [
                                'fields'   => [
                                    ListingTField::FEATURES
                                ]
                            ]
                        ],
                        '4' => ListingWidget::SEPARATOR,
                        '5' => [
                            ListingWidget::ADDITIONAL_INFORMATION => [
                                'fields'   => [
                                    ListingTField::ATTACHMENT_FILE
                                ]
                            ]
                        ],
                        '6' => ListingWidget::SEPARATOR,
                        '7' => [
                            ListingWidget::VIDEO => [
                                'fields'   => [
                                    ListingTField::VIDEO
                                ]
                            ]
                        ],
                        '8' => ListingWidget::SEPARATOR,
                        '9' => [
                            ListingWidget::RECENT_REVIEWS => [
                                'fields'   => [
                                    ListingTField::REVIEW
                                ]
                            ]
                        ],
                    ],
                    ListingWidget::SIDEBAR_SECTION => [
                        '1' => [
                            ListingWidget::HOURS => [
                                'fields'   => [
                                    ListingTField::HOURS_WORK
                                ]
                            ]
                        ],
                        '2' => ListingWidget::SEPARATOR,
                        '3' => [
                            ListingWidget::LOCATION => [
                                'fields'   => [
                                    ListingTField::LOCATIONS
                                ]
                            ]
                        ],
                        '4' => ListingWidget::SEPARATOR,
                        '5' => [
                            ListingWidget::SOCIAL_BUTTONS => [
                                'fields'   => [
                                    ListingTField::SOCIAL_NETWORK
                                ]
                            ]
                        ],
                        '6' => ListingWidget::SEPARATOR,
                        '7' => [
                            ListingWidget::FACEBOOK_FEED => [
                                'fields'   => [
                                    ListingTField::SOCIAL_NETWORK
                                ]
                            ]
                        ],
                        '8' => ListingWidget::SEPARATOR,
                        '9' => ListingWidget::SQUARE_BANNER
                    ]
                ],
                ListingTemplateTab::PHOTOS => [
                    ListingWidget::MAIN_SECTION => [
                        '1' => [
                            ListingWidget::PHOTO_GALLERY => [
                                'fields'   => [
                                    ListingTField::IMAGES
                                ]
                            ]
                        ]
                    ]
                ],
                ListingTemplateTab::REVIEWS => [
                    ListingWidget::MAIN_SECTION => [
                        '1' => [
                            ListingWidget::REVIEWS_PAGINATED => [
                                'fields'   => [
                                    ListingTField::REVIEW
                                ]
                            ]
                        ]
                    ],
                    ListingWidget::SIDEBAR_SECTION => [
                        '1' => ListingWidget::WIDE_SKYSCRAPER_BANNER
                    ]
                ],
                ListingTemplateTab::DEALS => [
                    ListingWidget::MAIN_SECTION => [
                        '1' => [
                            ListingWidget::ASSOCIATED_DEALS => [
                                'fields'   => [
                                    ListingTField::DEALS
                                ]
                            ]
                        ]
                    ]
                ],
                ListingTemplateTab::CLASSIFIEDS => [
                    ListingWidget::MAIN_SECTION => [
                        '1' => [
                            ListingWidget::ASSOCIATED_CLASSIFIEDS => [
                                'fields'   => [
                                    ListingTField::CLASSIFIEDS
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $standardWidgets;
    }

    /**
     * @return array
     */
    public function getCustomDefaultListingWidgets()
    {
        $standardWidgets = $this->getListingDefaultListingWidgets();

        return $standardWidgets;
    }

    /**
     * @param $listingTemplate
     * @param $customDefaultListingWidgets
     */
    public function buildTemplate($listingTemplate,$customDefaultListingWidgets)
    {

        $em = $this->container->get('doctrine')->getManager();
        $options = [];
        $counterWidgets = 1;
        $counterCategories =1;
        $counterTabs = 1;

        if(!empty($customDefaultListingWidgets['header'])) {
            $listingTemplateWidgetTitle = $customDefaultListingWidgets['header'];
            $content = null;
            if (is_array($listingTemplateWidgetTitle)) {
                $content = json_encode(current($listingTemplateWidgetTitle)['content']);
                $listingTemplateWidgetTitle = key($listingTemplateWidgetTitle);
            }
            $options["Widgets ".$counterWidgets++] = $listingTemplateWidgetTitle;
            /* @var $listingWidget ListingWidget */
            $listingWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findOneBy([
                'title'   => $listingTemplateWidgetTitle,
                'section' => 'header'
            ]);

            $this->saveListingWidget(
                $content,
                $listingTemplate,
                $listingWidget,
                null
            );
        }


        $order = 0;
        foreach ($customDefaultListingWidgets['tabs'] as $tab => $sections) {
            $listingTemplateTab = new ListingTemplateTab();
            $listingTemplateTab->setTitle($tab);
            $listingTemplateTab->setListingTemplate($listingTemplate);
            $listingTemplateTab->setOrder($order);
            $options['Tabs '.$counterTabs++] = $listingTemplateTab->getTitle();
            $em->persist($listingTemplateTab);
            $em->flush();

            foreach ($sections as $section => $listingWidgets) {
                foreach ($listingWidgets as $listingWidgetTitle) {
                    $content = null;
                    if (is_array($listingWidgetTitle)) {
                        $content = json_encode(current($listingWidgetTitle)['fields']);
                        $listingWidgetTitle = key($listingWidgetTitle);
                    }
                    $options["Widgets ".$counterWidgets++] = $listingWidgetTitle;
                    /* @var $listingWidget ListingWidget */
                    $listingWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findOneBy([
                        'title'   => $listingWidgetTitle,
                        'section' => $section
                    ]);
                    $this->saveListingWidget($content, $listingTemplate, $listingWidget, $listingTemplateTab);
                }
            }
            $order++;
        }

        $options ["Name"] = $listingTemplate->getTitle();
        $options ["Category"] = "All";
        $options ["Additional price"] ="yes";
        $options ["Disabled price"] ="no";
        $options ["Summary option"] = $listingTemplate->getSummaryTemplate();
        if($counterWidgets>1){
            $options ["Total of widgets"] =$counterWidgets-1;
        }else{
            $options ["Total of widgets"] =$counterWidgets;
        }
        $this->container->get('mixpanel.helper')->trackEvent('Created a new listing template', $options);
    }
}
