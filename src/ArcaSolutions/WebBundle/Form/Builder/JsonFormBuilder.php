<?php

namespace ArcaSolutions\WebBundle\Form\Builder;

use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\MultiDomainBundle\Services\Settings;
use ArcaSolutions\WebBundle\Services\SettingsMain;
use ArcaSolutions\WysiwygBundle\Services\ThemeService;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Build add fields mapped on json file to a Symfony Form.
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.0.00
 * @package ArcaSolutions\WebBundle\Form\Builder
 */
class JsonFormBuilder
{
    const PREFIX = 'custom_';

    protected $fieldDictionary = [
        'input_text' => TextType::class,
        'textarea'   => TextareaType::class,
        'checkbox'   => ChoiceType::class,
        'radio'      => ChoiceType::class,
        'select'     => ChoiceType::class,
    ];

    private $fields = [];

    /** @var TranslatorInterface */
    private $translator;
    /** @var string */
    private $folder;
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var SettingsMain */
    private $settingsMain;

    /** @var DoctrineRegistry */
    private $doctrine;

    /** @var ThemeService */
    private $themeService;
    /** @var \ArcaSolutions\CoreBundle\Services\Settings */
    private $settings1;
    private $leadFormCount;

    public function __construct(Settings $settings, FormFactoryInterface $formFactory, TranslatorInterface $translator, SettingsMain $settingsMain, DoctrineRegistry $doctrine, ThemeService $themeService, \ArcaSolutions\CoreBundle\Services\Settings $settings1)
    {
        $this->translator = $translator;
        $this->folder = $settings->getPath(true).'editor/lead/';
        $this->formFactory = $formFactory;
        $this->settingsMain = $settingsMain;
        $this->doctrine = $doctrine;
        $this->themeService = $themeService;
        $this->settings1 = $settings1;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(FormInterface $form)
    {
        return serialize($this->getFieldsWithValues($form, true));
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.04
     *
     * @param FormInterface $form
     * @param bool $allFields
     * @return array
     */
    public function getFieldsWithValues(FormInterface $form, $allFields = false)
    {
        /* Adjust the fields for serialize */
        $lead = [];
        foreach ($form->all() as $children => $childrenForm) {
            $name = $childrenForm->getConfig()->getOptions()['label'];

            /* Removes the field name prefix */
            $name = mb_strpos($name, self::PREFIX) !== false ? mb_substr($name, strlen(self::PREFIX)) : $name;

            /* Deletes fields that are not created by the form build */
            if (!$allFields && empty($this->getField($children))) {
                continue;
            }

            $key = Inflector::humanize($name, '-');
            if (array_key_exists($key, $lead)) {
                $key = '-'.$key;
            }

            if (is_array($childrenForm->getNormData())) {
                $field = $this->getField($children);
                $_value = PHP_EOL;

                if (!empty($field) && is_array($field['values'])) {
                    foreach ($field['values'] as $item => $response) {
                        if (!empty($response['value'])) {
                            $_response = !in_array(ucfirst($response['value']), $childrenForm->getNormData()) ?
                                $this->translator->trans('No') : $this->translator->trans('Yes');
                            $_value .= '- '.Inflector::humanize($response['value']);
                            $_value .= ': '.$_response.PHP_EOL;
                        }
                    }
                }
                $value = $_value;
            } else {
                $value = $childrenForm->getNormData();
            }

            $lead[$key] = $value.PHP_EOL;
        }

        return $lead;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.0.00
     *
     * @param string $name
     * @return array
     */
    private function getField($name)
    {
        if(!empty($this->fields[$this->leadFormCount])) {
            foreach ($this->fields[$this->leadFormCount] as $field) {
                if ($field['cssClass'] === 'undefined') {
                    continue;
                }

                $key = isset($field['values']) && !is_array($field['values']) ? 'values' : 'title';

                if (Inflector::friendly_title($field[$key]) === mb_substr($name, strlen(self::PREFIX))) {
                    return $field;
                }
            }
        }

        return [];
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param FormInterface|null $form
     * @param string $jsonFileName
     * @return FormInterface
     */
    public function generate(FormInterface $form = null, $jsonFileName = '', $widgetType = '')
    {
        $this->leadFormCount++;

        if ($form === null) {
            $form = $this->formFactory->create(FormType::class, null);
        }

        $filePath = $this->folder.$jsonFileName;

        if(empty($this->fields[$this->leadFormCount])) {
            if (file_exists($filePath)) {
                $this->fields[$this->leadFormCount] = json_decode(file_get_contents($filePath), true);
            } else if ($widgetType == 'leadgen') {
                $fields = [
                    [
                        'cssClass' => 'input_text',
                        'required' => 'true',
                        'values'   => 'Name'
                    ],
                    [
                        'cssClass' => 'input_text',
                        'required' => 'true',
                        'values'   => 'Email'
                    ],
                    [
                        'cssClass' => 'input_text',
                        'required' => 'true',
                        'values'   => 'Phone Number'
                    ],
                    [
                        'cssClass' => 'input_text',
                        'required' => 'true',
                        'values'   => 'Subject'
                    ],
                    [
                        'cssClass' => 'textarea',
                        'required' => 'true',
                        'values'   => 'Message'
                    ],
                ];

                $this->fields[$this->leadFormCount] = $fields;
            }
        }
        if(!empty($this->fields[$this->leadFormCount])) {
            foreach ($this->fields[$this->leadFormCount] as $field) {
                /* Ignores type undefined */
                if ($field['cssClass'] === 'undefined') {
                    continue;
                }

                if (empty($field['values'])) {
                    continue;
                }

                $type = $field['cssClass'];
                $formType = $this->fieldDictionary[$field['cssClass']];

                $options = [
                    'required'    => false,
                    'constraints' => [],
                    'attr'        => [
                        'data-type' => $type
                    ]
                ];

                if ($field['required'] === 'true') {
                    $options['required'] = true;
                    $options['constraints'] = new NotBlank(['message' => 'The field is required']);
                }

                if (!is_array($field['values'])) {
                    $options['label'] = Inflector::humanize($field['values']);
                    $options['attr']['placeholder'] = Inflector::humanize($field['values']);

                    if ($field['cssClass'] === 'textarea') {
                        $options['attr']['rows'] = 5;
                    }

                    $fieldName = self::PREFIX.Inflector::friendly_title($field['values']);

                    $form->add($fieldName, $formType, $options);

                    continue;
                }

                $choices = [];
                foreach ($field['values'] as $key => $item) {
                    if (!empty($item['value'])) {
                        $choices[Inflector::humanize($item['value'])] = $item['value'];
                    }
                }

                $options['label'] = Inflector::humanize($field['title']);
                $options['choices'] = $choices;
                $options['multiple'] = $type === 'checkbox';
                $options['expanded'] = $type !== 'select';
                $options['placeholder'] = false;
                if($type === 'checkbox') {
                    $options['attr']['data-required'] = $field['required'];
                }

                $fieldName = self::PREFIX.Inflector::friendly_title($field['title']);

                $form->add($fieldName, $formType, $options);
            }
            if(file_exists($filePath)){
                //leadForm
                if($jsonFileName !== "save.json"){
                    if ($this->settings1->getDomainSetting('google_recaptcha_status') === 'on') {
                        $options = ['label' => false];
                    } else {
                        $options = [
                            'label' => false,
                            'reload' => true,
                            'as_url' => true,
                        ];

                    }
                    $form->add('captchaLeadForm', CaptchaType::class, $options);
                }
            }
        }
        $consentSettings = $this->settingsMain->getSettingsConsent();

        if($consentSettings->getValue() =="on"){
            $label = 'I understand that information I enter will be stored and shared with relevant members of the site and that I may be contacted by these members and/or the admin of the website.';

            if(file_exists($filePath)){
                //leadForm
                if($jsonFileName !== "save.json"){
                    //leadForm
                    $idWidget = str_replace([".json","save_"],"",$jsonFileName);
                    $repository = $this->doctrine->getRepository('WysiwygBundle:PageWidget', 'domain');
                    $pageWidget = $repository->find($idWidget);
                }// form contactUs
                else{
                    $theme = $this->themeService->getSelectedTheme();
                    $repository = $this->doctrine->getRepository('WysiwygBundle:Widget', 'domain');
                    $widget = $repository->findOneBy(['title'=>'Contact form']);
                    $repository = $this->doctrine->getRepository('WysiwygBundle:PageWidget', 'domain');
                    $pageWidget = $repository->findOneBy(['themeId'=>$theme->getId(),'widgetId'=>$widget->getId()]);
                }
                //convert string content in json
                $contents = json_decode($pageWidget->getContent(), true);
                if(isset($contents['placeholderConsent'])){
                    $label = $contents['placeholderConsent']['value'];
                }

            }
            $form->add('consent', CheckboxType::class, [
                'label' =>  /** @Ignore */$this->translator->trans($label, [], 'consent'),
                'required' => true,
                'constraints' => new IsTrue(['message' => $this->translator->trans('You must agree to the consent terms.',[],'consent')])
            ]);
        }

        return $form;
    }
}
