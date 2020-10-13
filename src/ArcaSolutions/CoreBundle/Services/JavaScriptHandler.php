<?php

namespace ArcaSolutions\CoreBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class JavaScriptHandler
{
    private static $scriptTemplate = "::blocks/utility/javascripthandler.html.twig";

    /**
     * Contains the path of js files to be included
     * example: "js/filters.js"
     * @var string[]
     */
    private $externalFiles = [];

    /**
     * Contains the path of JS TWIG files
     * example: "::js/filters.js.twig"
     * @var string[]
     */
    private $blocks = [];

    /**
     * Contains the path of JS TWIG files and their following exclusive parameters
     * example: "['::js/filters.js.twig'] = ['block' =>'::js/filters.js.twig','params'=> [ 'parameter_id' => parameter_value ]]"
     * @var array
     */
    private $blocksWithParams = [];

    /**
     * Contains the path of JS TWIG files and their following exclusive parameters, allowing multiple rendering
     * example: "['::js/filters.js.twig'] = ['block' =>'::js/filters.js.twig','identifiers'=> [
     * 'blockIdentifier1' => [ 'parameter_id' => parameter_value1, 'other_parameter_id' => other_parameter_value1 ],
     * 'blockIdentifier2' => [ 'parameter_id' => parameter_value2, 'other_parameter_id' => other_parameter_value2 ],
     * ]]"
     * @var array
     */
    private $blocksWithIdentifiedParams = [];

    /**
     * Contains the list of parameters to be used within the rendering of the $blocks twigs
     * @var string[]
     */
    private $parameters = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    function __construct($container)
    {
        $this->container = $container;
    }

    //region External Files
    /**
     * @return array
     */
    public function getExternalFiles()
    {
        return $this->externalFiles;
    }

    /**
     * @param $id
     * @return $this JavaScriptHandler
     */
    public function removeJSExternalFile($id)
    {
        unset($this->externalFiles[$id]);
        return $this;
    }

    /**
     * @param $id
     * @return string
     */
    public function getJSExternalFile($id)
    {
        return isset($this->externalFiles[$id]) ? $this->externalFiles[$id] : null;
    }

    /**
     * @param $file
     * @return $this JavaScriptHandler
     */
    public function addJSExternalFile($file)
    {
        $this->externalFiles[$file] = $file;
        return $this;
    }
    //endregion

    //region Blocks
    /**
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @return array
     */
    public function getBlocksWithIdentifiedParams()
    {
        return $this->blocksWithIdentifiedParams;
    }


    /**
     * @return array
     */
    public function getBlocksWithParams()
    {
        return $this->blocksWithParams;
    }

    /**
     * @param $id
     * @return $this JavaScriptHandler
     */
    public function removeJSBlock($id)
    {
        if(array_key_exists($id, $this->blocks)) {
            unset($this->blocks[$id]);
        }
        if(array_key_exists($id, $this->blocksWithParams)) {
            unset($this->blocksWithParams);
        }
        return $this;
    }

    /**
     * @param $id
     * @return string
     */
    public function getJSBlock($id)
    {
        return isset($this->blocks[$id]) ? $this->blocks[$id] : null;
    }

    /**
     * @param $code
     * @return $this JavaScriptHandler
     */
    public function addJSBlock($code)
    {
        $this->blocks[$code] = $code;
        $this->blocksWithParams[$code] = [
            'block' => $code,
            'params' => []
        ];
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @param $value
     * @return $this
     */
    public function addJSBlockWithParameter($path, $id, $value)
    {
        $this->blocks[$path] = $path;
        $this->blocksWithParams[$path] = [
            'block' => $path,
            'params' => [
                $id => $value
            ]
        ];
        return $this;
    }

    /**
     * @param $path
     * @param array $parameters
     * @return $this
     */
    public function addJSBlockWithParameters($path, $parameters)
    {
        $this->blocks[$path] = $path;
        $this->blocksWithParams[$path] = [
            'block' => $path
        ];
        if($parameters!==null && is_array($parameters)) {
            $this->blocksWithParams[$path]['params'] = $parameters;
        } else {
            $this->blocksWithParams[$path]['params'] = [];
        }
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @param $value
     * @return $this
     */
    public function addJSBlockParameter($path, $id, $value)
    {
        if(!array_key_exists($path,$this->blocksWithParams)){
            $this->addJSBlockWithParameter($path, $id, $value);
        } else {
            if(!array_key_exists('params',$this->blocksWithParams[$path])) {
                $this->blocksWithParams[$path]['params'] = [];
            }
            $this->blocksWithParams[$path]['params'][$id] = $value;
        }
        return $this;
    }

    /**
     * @param $path
     * @param $parameters
     * @return $this
     */
    public function replaceJSBlockParameters($path, $parameters)
    {
        if(!array_key_exists($path,$this->blocksWithParams) && $parameters!==null && is_array($parameters)){
            $this->blocksWithParams[$path]['params'] = $parameters;
        }
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @return $this
     */
    public function removeJSBlockParameter($path, $id)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('params',$this->blocksWithParams[$path])) {
                if(array_key_exists($id,$this->blocksWithParams[$path]['params'])) {
                    unset($this->blocksWithParams[$path]['params'][$id]);
                }
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function removeJSBlockParameters($path)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('params',$this->blocksWithParams[$path])) {
                $this->blocksWithParams[$path]['params'] = [];
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @return array
     */
    public function getJSBlockParameters($path)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('params',$this->blocksWithParams[$path])) {
                return !is_array($this->blocksWithParams[$path]['params']) ? [] : $this->blocksWithParams[$path]['params'];
            }
        }
        return [];
    }

    /**
     * @param $path
     * @param $id
     * @return mixed|null
     */
    public function getJSBlockParameter($path, $id)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('params',$this->blocksWithParams[$path])) {
                if(array_key_exists($id,$this->blocksWithParams[$path]['params'])) {
                    return $this->blocksWithParams[$path]['params'][$id];
                }
            }
        }
        return null;
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @param $value
     * @return $this
     */
    public function addUniqueJSBlockWithParameter($path, $id, $paramId, $value)
    {
        if(!array_key_exists($path,$this->blocksWithParams)) {
            $identifiedParams = [
                $paramId => $value
            ];
            $this->addUniqueJSBlockWithParameters($path, $id, $identifiedParams);
        } else {
            if(empty($this->blocksWithIdentifiedParams[$path]['identifiers']))
            {
                $this->blocksWithIdentifiedParams[$path]['identifiers'] = [
                    $id => [
                        $paramId => $value
                    ]
                ];
            } else {
                $this->blocksWithIdentifiedParams[$path]['identifiers'][$id] = [
                    $paramId => $value
                ];
            }
        }

        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @param array $identifiedParams
     * @return $this
     */
    public function addUniqueJSBlockWithParameters($path, $id, $identifiedParams)
    {
        if($identifiedParams===null || !is_array($identifiedParams)) {
            $identifiedParams = [];
        }

        if(!array_key_exists($path,$this->blocksWithIdentifiedParams)){
            $this->blocksWithIdentifiedParams[$path] = [
                'block' => $path,
                'identifiers' => [
                    $id => $identifiedParams
                ]
            ];
        } else {
            if(empty($this->blocksWithIdentifiedParams[$path]['identifiers']))
            {
                $this->blocksWithIdentifiedParams[$path]['identifiers'] = [
                    $id => $identifiedParams
                ];
            } else {
                $this->blocksWithIdentifiedParams[$path]['identifiers'][$id] = $identifiedParams;
            }
        }

        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @param $value
     * @return $this
     */
    public function addUniqueJSBlockParameter($path, $id, $paramId, $value)
    {
        if(!array_key_exists($path,$this->blocksWithParams)){
            $this->addUniqueJSBlockWithParameter($path, $id, $paramId, $value);
        } else {
            if(empty($this->blocksWithIdentifiedParams[$path]['identifiers']))
            {
                $this->blocksWithIdentifiedParams[$path]['identifiers'] = [
                    $id => [
                        $paramId => $value
                    ]
                ];
            } else {
                if(empty($this->blocksWithIdentifiedParams[$path]['identifiers'][$id]))
                {
                    $this->blocksWithIdentifiedParams[$path]['identifiers'][$id] = [
                        $paramId => $value
                    ];
                } else {
                    $this->blocksWithIdentifiedParams[$path]['identifiers'][$id][$paramId] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @param $identifiedParams
     * @return $this
     */
    public function replaceUniqueJSBlockParameters($path, $id, $identifiedParams)
    {
        if(!array_key_exists($path,$this->blocksWithIdentifiedParams)){
            $this->addUniqueJSBlockWithParameters($path, $id, $identifiedParams);
        } else {
            if(empty($this->blocksWithIdentifiedParams[$path]['identifiers']))
            {
                $this->blocksWithIdentifiedParams[$path]['identifiers'] = [
                    $id => $identifiedParams
                ];
            } else {
                $this->blocksWithIdentifiedParams[$path]['identifiers'][$id] = $identifiedParams;
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @return $this
     */
    public function removeUniqueJSBlockParameter($path, $id, $paramId)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('identifiers',$this->blocksWithParams[$path])) {
                if(array_key_exists($id,$this->blocksWithParams[$path]['identifiers'])) {
                    if(array_key_exists($paramId,$this->blocksWithParams[$path]['identifiers'][$id])) {
                        unset($this->blocksWithParams[$path]['identifiers'][$id][$paramId]);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @return $this
     */
    public function removeUniqueJSBlockParameters($path, $id)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('identifiers',$this->blocksWithParams[$path])) {
                if(array_key_exists($id,$this->blocksWithParams[$path]['identifiers'])) {
                    $this->blocksWithParams[$path]['identifiers'][$id] = [];
                }
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @return $this
     */
    public function removeUniqueJSBlock($path, $id)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('identifiers',$this->blocksWithParams[$path])) {
                if(array_key_exists($id,$this->blocksWithParams[$path]['identifiers'])) {
                    unset($this->blocksWithParams[$path]['identifiers'][$id]);
                    if(empty($this->blocksWithParams[$path]['identifiers']))
                    {
                        unset($this->blocksWithParams[$path]);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @param $id
     * @return array
     */
    public function getUniqueJSBlockParameters($path, $id)
    {
        if(array_key_exists($path,$this->blocksWithIdentifiedParams)){
            if(array_key_exists('identifiers',$this->blocksWithIdentifiedParams[$path])) {
                if(array_key_exists($id,$this->blocksWithIdentifiedParams[$path]['identifiers'])) {
                    return !is_array($this->blocksWithIdentifiedParams[$path]['identifiers'][$id])? [] : $this->blocksWithIdentifiedParams[$path]['identifiers'][$id];
                }
            }
        }

        return [];
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @return mixed|null
     */
    public function getUniqueJSBlockParameter($path, $id, $paramId)
    {
        if(array_key_exists($path,$this->blocksWithParams)){
            if(array_key_exists('identifiers',$this->blocksWithParams[$path])) {
                if(array_key_exists($id,$this->blocksWithParams[$path]['identifiers'])) {
                    if(array_key_exists($paramId,$this->blocksWithParams[$path]['identifiers'][$id])) {
                        return $this->blocksWithParams[$path]['identifiers'][$id][$paramId];
                    }
                }
            }
        }

        return null;
    }

    //endregion

    //region Parameters
    /**
     * @return array
     */
    public function getTwigParameters()
    {
        return !isset($this->parameters) ? [] : (!is_array($this->parameters) ? [] : $this->parameters);
    }

    /**
     * @param $id
     * @return $this JavaScriptHandler
     */
    public function removeTwigParameter($id)
    {
        unset($this->parameters[$id]);
        return $this;
    }

    /**
     * @param $id
     * @return string
     */
    public function getTwigParameter($id)
    {
        return isset($this->parameters[$id]) ? $this->parameters[$id] : null;
    }

    /**
     * @param $id
     * @param $code
     * @return $this JavaScriptHandler
     */
    public function addTwigParameter($id, $code)
    {
        $input = null;

        if( strpos($id,".") !== false ){
            Utility::assignArrayByPath($input, $id, $code);

            $id = key($input);
            $code = $input[$id];
        }

        if( isset($this->parameters[$id]) ){
            $this->parameters[$id] = array_merge_recursive( (array)$this->parameters[$id], (array)$code );
        } else {
            $this->parameters[$id] = $code;
        }

        return $this;
    }
    //endregion

    /**
     * @return string
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    public function render()
    {
        $twig = $this->container->get("twig");

        foreach($this->blocksWithIdentifiedParams as $block => $blockWithIdentifiedParams){
            if(!empty($block) && array_key_exists('identifiers', $blockWithIdentifiedParams) && is_array($blockWithIdentifiedParams['identifiers']))
            {
                foreach ($blockWithIdentifiedParams['identifiers'] as $paramsIdentifier=>$identifiedParams){
                    $blockParams = $this->getJSBlockParameters($block);
                    $uniqueBlockIdentifiedParams = $this->getUniqueJSBlockParameters($block,$paramsIdentifier);
                    $resultingParams = array_replace_recursive($blockParams,$uniqueBlockIdentifiedParams);
                    $this->replaceUniqueJSBlockParameters($block,$paramsIdentifier,$resultingParams);
                    unset($anyBlockParams,$blockParams,$uniqueBlockIdentifiedParams,$resultingParams);
                }
            }
        }

        foreach($this->blocks as $block) {
            $this->replaceJSBlockParameters($block, array_replace_recursive($this->getTwigParameters(), $this->getJSBlockParameters($block)));
        }

        $this->addTwigParameter( "handler", $this );

        return $twig->render(self::$scriptTemplate, $this->getTwigParameters());
    }
}
