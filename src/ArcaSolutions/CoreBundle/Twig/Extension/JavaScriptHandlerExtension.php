<?php

namespace ArcaSolutions\CoreBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Services\JavaScriptHandler;
use Exception;
use Symfony\Component\DependencyInjection\Container;
use Twig_Extension;
use Twig_SimpleFunction;

class JavaScriptHandlerExtension extends Twig_Extension
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'renderJS',
                [$this, 'render'],
                ['is_safe' => ['all']]
            ),
            new Twig_SimpleFunction(
                'addJSTwig',
                [$this, 'addJSTwig']
            ),
            new Twig_SimpleFunction(
                'addUniqueJSTwigWithParam',
                [$this, 'addUniqueJSTwigWithParam']
            ),
            new Twig_SimpleFunction(
                'addUniqueJSTwigWithParams',
                [$this, 'addUniqueJSTwigWithParams']
            ),
            new Twig_SimpleFunction(
                'addUniqueJSTwigParam',
                [$this, 'addUniqueJSTwigParam']
            ),
            new Twig_SimpleFunction(
                'getUniqueJSTwigParam',
                [$this, 'getUniqueJSTwigParam']
            ),
            new Twig_SimpleFunction(
                'getUniqueJSTwigParams',
                [$this, 'getUniqueJSTwigParams']
            ),
            new Twig_SimpleFunction(
                'removeUniqueJSTwigParam',
                [$this, 'removeUniqueJSTwigParam']
            ),
            new Twig_SimpleFunction(
                'removeUniqueJSTwigParams',
                [$this, 'removeUniqueJSTwigParams']
            ),
            new Twig_SimpleFunction(
                'removeUniqueJSTwig',
                [$this, 'removeUniqueJSTwig']
            ),
            new Twig_SimpleFunction(
                'replaceUniqueJSTwigParams',
                [$this, 'replaceUniqueJSTwigParams']
            ),
            new Twig_SimpleFunction(
            'addJSTwigWithParam',
                [$this, 'addJSTwigWithParam']
            ),
            new Twig_SimpleFunction(
                'addJSTwigWithParams',
                [$this, 'addJSTwigWithParams']
            ),
            new Twig_SimpleFunction(
                'addJSTwigParam',
                [$this, 'addJSTwigParam']
            ),
            new Twig_SimpleFunction(
                'getJSTwigParam',
                [$this, 'getJSTwigParam']
            ),
            new Twig_SimpleFunction(
                'getJSTwigParams',
                [$this, 'getJSTwigParams']
            ),
            new Twig_SimpleFunction(
                'removeJSTwigParam',
                [$this, 'removeJSTwigParam']
            ),
            new Twig_SimpleFunction(
                'removeJSTwigParams',
                [$this, 'removeJSTwigParams']
            ),
            new Twig_SimpleFunction(
                'replaceJSTwigParams',
                [$this, 'replaceJSTwigParams']
            ),
            new Twig_SimpleFunction(
                'addJSTwigParameter',
                [$this, 'addJSTwigParameter']
            ),
            new Twig_SimpleFunction(
                'addJSFile',
                [$this, 'addJSFile']
            )
        ];
    }

    /**
     * Twig extension that renders the banners
     *
     * @return string
     * @throws Exception
     */
    public function render()
    {
        return $this->container->get("javascripthandler")->render();
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function addJSTwig($path)
    {
        $this->container->get("javascripthandler")->addJSBlock($path);
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @param $value
     * @throws Exception
     */
    public function addUniqueJSTwigWithParam($path, $id, $paramId, $value)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->addUniqueJSBlockWithParameter($path, $id, $paramId, $value);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $params
     * @throws Exception
     */
    public function addUniqueJSTwigWithParams($path, $id, $params)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->addUniqueJSBlockWithParameters($path, $id, $params);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @param $value
     * @throws Exception
     */
    public function addUniqueJSTwigParam($path, $id, $paramId, $value)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->addUniqueJSBlockParameter($path, $id, $paramId, $value);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @throws Exception
     */
    public function getUniqueJSTwigParam($path, $id, $paramId)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->getUniqueJSBlockParameter($path, $id, $paramId);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @throws Exception
     */
    public function getUniqueJSTwigParams($path,$id)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->getUniqueJSBlockParameters($path,$id);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @throws Exception
     */
    public function removeUniqueJSTwigParam($path, $id, $paramId)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->removeUniqueJSBlockParameter($path, $id, $paramId);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $paramId
     * @throws Exception
     */
    public function removeUniqueJSTwigParams($path, $id, $paramId)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->removeUniqueJSBlockParameters($path, $id, $paramId);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @throws Exception
     */
    public function removeUniqueJSTwig($path, $id){
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->removeUniqueJSBlock($path, $id);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $params
     * @throws Exception
     */
    public function replaceUniqueJSTwigParams($path, $id, $params)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->replaceUniqueJSBlockParameters($path, $id, $params);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $value
     * @throws Exception
     */
    public function addJSTwigWithParam($path, $id, $value)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->addJSBlockWithParameter($path, $id, $value);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $params
     * @throws Exception
     */
    public function addJSTwigWithParams($path, $params)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->addJSBlockWithParameters($path, $params);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @param $value
     * @throws Exception
     */
    public function addJSTwigParam($path, $id, $value)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->addJSBlockParameter($path, $id, $value);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @throws Exception
     */
    public function getJSTwigParam($path, $id)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->getJSBlockParameter($path, $id);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function getJSTwigParams($path)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->getJSBlockParameters($path);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $id
     * @throws Exception
     */
    public function removeJSTwigParam($path, $id)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->removeJSBlockParameter($path, $id);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function removeJSTwigParams($path)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->removeJSBlockParameters($path);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $path
     * @param $params
     * @throws Exception
     */
    public function replaceJSTwigParams($path, $params)
    {
        /**
         * @var JavaScriptHandler $jsHandlerService
         */
        $jsHandlerService = $this->container->get("javascripthandler");
        if(!empty($jsHandlerService)) {
            $jsHandlerService->replaceJSBlockParameters($path, $params);
        }
        unset($jsHandlerService);
    }

    /**
     * @param $id
     * @param $value
     * @throws Exception
     */
    public function addJSTwigParameter($id, $value)
    {
        $this->container->get("javascripthandler")->addTwigParameter($id, $value);
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function addJSFile($path)
    {
        $this->container->get("javascripthandler")->addJSExternalFile($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'javaScriptHandler';
    }
}
