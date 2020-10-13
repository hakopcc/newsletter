<?php

namespace ArcaSolutions\CoreBundle\Twig\Extension;

use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Twig_Extension;
use Twig_SimpleFunction;

class CKEditorExtension extends Twig_Extension
{
    private $_cdnScriptRendered = false;
    private $_dtdScriptRendered = false;
    /**
     * @var string[] $_renderedTextAreaIds
     */
    private $_renderedTextAreaIds = [];

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'CKEditor';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('renderCKEditor', [$this, 'renderCKEditor'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ])
        ];
    }

    /**
     * @param Twig_Environment $twig_Environment
     * @param $textAreaId
     * @param string $textAreaName
     * @param int $textAreaRows
     * @param int $textAreaCols
     * @param string $textAreaCssClass
     * @param string $textAreaCssStyle
     * @param string $textAreaPlaceHolder
     * @param string $textAreaContent
     * @param bool $renderFullPage
     * @param string $lang
     * @param bool $simpleToolbar
     * @param null $appendConfigJson
     * @param null $customConfig
     * @return string
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function renderCKEditor(Twig_Environment $twig_Environment,
                                   $textAreaId,
                                   $textAreaName = '',
                                   $textAreaRows = 4,
                                   $textAreaCols = 20,
                                   $textAreaCssClass = '',
                                   $textAreaCssStyle = '',
                                   $textAreaPlaceHolder = '',
                                   $textAreaContent = '',
                                   $renderFullPage = false,
                                   $lang = 'en_us',
                                   $simpleToolbar = false,
                                   $appendConfigJson = null,
                                   $customConfig = null)
    {
        if(empty($textAreaId)){
            throw new Twig_Error_Runtime('renderCKEditor twig function demands the first parameter (textAreaId)');
        }
        if(empty($textAreaName)){
            $textAreaName = $textAreaId;
        }

        $includeLoadScriptTag = array_search($textAreaId, $this->_renderedTextAreaIds)===false;

        $ckeditorDataObject = [
            'includeCdnScriptTag' => !$this->_cdnScriptRendered,
            'includeDtdScriptTag'=> !$this->_dtdScriptRendered,
            'textAreaId' => $textAreaId,
            'textAreaName' => $textAreaName,
            'textAreaRows' => $textAreaRows,
            'textAreaCols' => $textAreaCols,
            'textAreaCssClass' => $textAreaCssClass,
            'textAreaCssStyle' => $textAreaCssStyle,
            'textAreaContent' => $textAreaContent,
            'textAreaPlaceHolder' => $textAreaPlaceHolder,
            'includeLoadScriptTag' => $includeLoadScriptTag,
        ];

        $configJsonDecodedJsonObj = (object) [];
        $configJsonDecodedJsonObj->language = $lang;
        $configJsonDecodedJsonObj->removeButtons = '';
        if($renderFullPage){
            $configJsonDecodedJsonObj->fullPage = true;
        }
        if(!empty($customConfig)) {
            $configJsonDecodedJsonObj->customConfig = $customConfig;
        } else {
            $toolbarClipboardItems = $simpleToolbar?(array)['Cut', 'Copy', 'Paste']:(array)['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'];
            $toolbarStylesItems = $simpleToolbar?(array)['Format', 'Font', 'FontSize']:(array)['Format', 'Font', 'FontSize', 'TextColor','BGColor'];
            $configJsonDecodedJsonObj->toolbar = (array)[];
            $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'basicstyles','items'=>(array)['Bold', 'Italic', 'Underline', 'Strike']];
            $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'clipboard','items'=>$toolbarClipboardItems];
            $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'links','items'=>(array)['Link', 'Unlink']];
            if(!$simpleToolbar){
                $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'insert','items'=>(array)['Image', 'Table', 'HorizontalRule']];
            }
            $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'paragraph','items'=>(array)['NumberedList', 'BulletedList', '-', 'Blockquote']];
            if(!$simpleToolbar){
                $configJsonDecodedJsonObj->toolbar[] = '/';
                $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'justify','items'=>(array)['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']];
            }
            $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'styles','items'=>$toolbarStylesItems];
            if(!$simpleToolbar){
                $configJsonDecodedJsonObj->toolbar[] = '/';
                $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'tools','items'=>(array)['Maximize']];
                $configJsonDecodedJsonObj->toolbar[] = (object)['name'=> 'document','items'=>(array)['Source']];
                $configJsonDecodedJsonObj->extraPlugins = 'image2,uploadimage,colorbutton,font,justify';
                $configJsonDecodedJsonObj->filebrowserImageUploadUrl = '/ckeditor.php?uploadType=image';
                $configJsonDecodedJsonObj->filebrowserUploadUrl = '/ckeditor.php';
                $configJsonDecodedJsonObj->filebrowserUploadMethod  = 'form';
            } else {
                $configJsonDecodedJsonObj->extraPlugins = 'colorbutton,font';
            }
            $configJsonDecodedJsonObj->allowedContent = true;

            if(!empty($appendConfigJson)){
                $decodedAppendConfigJsonObj = json_decode($appendConfigJson);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new Twig_Error_Runtime(json_last_error_msg(), json_last_error());
                }
                if(property_exists($decodedAppendConfigJsonObj, 'language')){
                    $configJsonDecodedJsonObj->language = $decodedAppendConfigJsonObj->language;
                    unset($decodedAppendConfigJsonObj->language);
                }
                if(property_exists($decodedAppendConfigJsonObj, 'fullPage')){
                    $configJsonDecodedJsonObj->fullPage = $decodedAppendConfigJsonObj->fullPage;
                    unset($decodedAppendConfigJsonObj->fullPage);
                }
                if(property_exists($decodedAppendConfigJsonObj, 'customConfig'))
                {
                    $configJsonDecodedJsonObj->customConfig = $decodedAppendConfigJsonObj->customConfig;
                    unset($decodedAppendConfigJsonObj->customConfig);
                    if(property_exists($configJsonDecodedJsonObj, 'toolbar')) {
                        unset($configJsonDecodedJsonObj->toolbar);
                    }
                    if(property_exists($configJsonDecodedJsonObj, 'extraPlugins')) {
                        unset($configJsonDecodedJsonObj->extraPlugins);
                    }
                    if(property_exists($configJsonDecodedJsonObj, 'allowedContent')) {
                        unset($configJsonDecodedJsonObj->allowedContent);
                    }
                    foreach($configJsonDecodedJsonObj as $property=>$propertyValue)
                    {
                        if($property=='language' || $property=='fullPage' || $property=='customConfig')
                        {
                            continue;
                        }
                        unset($configJsonDecodedJsonObj[$property]);
                    }
                } else {
                    if(property_exists($decodedAppendConfigJsonObj, 'toolbar')) {
                        if(!empty($decodedAppendConfigJsonObj->toolbar)) {
                            if (is_array($decodedAppendConfigJsonObj->toolbar)) {
                                if (empty($configJsonDecodedJsonObj->toolbar)) {
                                    $configJsonDecodedJsonObj->toolbar = $decodedAppendConfigJsonObj->toolbar;
                                } else {
                                    foreach ($decodedAppendConfigJsonObj->toolbar as $toolbarItem) {
                                        if (is_object($toolbarItem)) {
                                            if (property_exists($toolbarItem, 'name') && property_exists($toolbarItem, 'items') && is_array($toolbarItem->items)) {
                                                $newToolbarItem=true;
                                                foreach ($configJsonDecodedJsonObj->toolbar as $originalToolbarIndex=>$originalToolbarItem) {
                                                    if (is_object($originalToolbarItem)) {
                                                        if (property_exists($originalToolbarItem, 'name') && property_exists($originalToolbarItem, 'items') && is_array($originalToolbarItem->items)) {
                                                            if ($originalToolbarItem->name == $toolbarItem->name){
                                                                $configJsonDecodedJsonObj->toolbar[$originalToolbarIndex]->items =array_merge($originalToolbarItem->items, array_diff($toolbarItem->items, $originalToolbarItem->items));
                                                                $newToolbarItem=false;
                                                            }
                                                        }
                                                    }
                                                }
                                                if($newToolbarItem){
                                                    $configJsonDecodedJsonObj->toolbar[] = $toolbarItem;
                                                }
                                                unset($newToolbarItem);
                                            }
                                        }
                                    }
                                }
                            }
                            unset($decodedAppendConfigJsonObj->toolbar);
                        }
                    }
                    if(property_exists($decodedAppendConfigJsonObj, 'extraPlugins')) {
                        if(is_string($decodedAppendConfigJsonObj->extraPlugins)) {
                            if (empty($configJsonDecodedJsonObj->extraPlugins)) {
                                $configJsonDecodedJsonObj->extraPlugins = $decodedAppendConfigJsonObj->extraPlugins;
                            } else {
                                $originalExtraPluginsArray = explode(',', $configJsonDecodedJsonObj->extraPlugins);
                                $extraPluginsArray = explode(',',$decodedAppendConfigJsonObj->extraPlugins);
                                $configJsonDecodedJsonObj->extraPlugins = implode( ',', array_merge($originalExtraPluginsArray, array_diff($extraPluginsArray, $originalExtraPluginsArray)));
                            }
                        }
                        unset($decodedAppendConfigJsonObj->extraPlugins);
                    }
                    if(property_exists($decodedAppendConfigJsonObj, 'allowedContent')) {
                        $configJsonDecodedJsonObj->allowedContent = $decodedAppendConfigJsonObj->allowedContent;
                        unset($decodedAppendConfigJsonObj->allowedContent);
                    }
                    foreach($decodedAppendConfigJsonObj as $property=>$propertyValue)
                    {
                        $configJsonDecodedJsonObj->$property = $propertyValue;
                        unset($decodedAppendConfigJsonObj->$property);
                    }
                }
            }
        }

        $configJsonEncodedJsonString = json_encode($configJsonDecodedJsonObj);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Twig_Error_Runtime(json_last_error_msg(), json_last_error());
        }

        $ckeditorDataObject['configJson']=$configJsonEncodedJsonString;

        if($includeLoadScriptTag) {
            $this->_renderedTextAreaIds[] = $textAreaId;
        }

        if(!$this->_cdnScriptRendered){
            $this->_cdnScriptRendered = true;
        }

        if(!$this->_dtdScriptRendered){
            $this->_dtdScriptRendered = true;
        }

        return $twig_Environment->render(':blocks/utility:ckeditor.html.twig', [
            'ckeditor'        => $ckeditorDataObject
        ]);
    }
}
