<?php

namespace ArcaSolutions\ModStoresBundle\Plugins;

use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\ModStoresBundle\Traits\ComposerMetadataTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BasePluginBundle
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
abstract class AbstractPluginBundle extends Bundle
{
    use ComposerMetadataTrait;

    /**
     * Returns install command
     *
     * @param $input
     * @param $output
     * @return EdirectoryModstoresInstallCommand
     */
    public function getInstallCommand($input, $output)
    {
        $commandClass = $this->getNamespace().'\\Command\\EdirectoryModstoresInstallCommand';

        return new $commandClass($input, $output, $this);
    }

    /**
     * Returns bundle config folder path
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->getResourcePath().'/config';
    }

    /**
     * Returns bundle Resource folder path
     *
     * @return string
     */
    public function getResourcePath()
    {
        return $this->getPath().'/Resources';
    }

    /**
     * Returns full qualified namespace
     *
     * @return string
     */
    public function getQualifiedNamespace()
    {
        return $this->getNamespace().'\\'.$this->getName();
    }

    /**
     * Returns full qualified namespace
     *
     * @return string
     */
    public function getFullyQualifiedNamespace()
    {
        return '\\'.$this->getNamespace().'\\'.$this->getName();
    }

    /**
     * Checks if current loaded page is from sitemgr area
     *
     * @return boolean
     */
    protected function isSitemgr()
    {
        $request = Request::createFromGlobals();

        $alias = $this->container->getParameter('alias_sitemgr_module');

        // verify if sitemgr alias from real URL as well as http referer in case of ajax request
        return (
            (strpos($request->getUri(), $alias) !== false) ||
            ($request->isXmlHttpRequest() === true && strpos($request->server->get('HTTP_REFERER'), $alias) !== false)
        );
    }

    /**
     * Returns the current ISOLang between if user is in front-end or sitemanager
     *
     * @return string
     */
    protected function getCurrentISOLang()
    {
        $returnValue = 'en';
        $isSiteMgr = $this->isSitemgr();
        /** @var LanguageHandler $languageHandler */
        $languageHandler = $this->container->get('languagehandler');
        if ($languageHandler !== null) {
            $locale = null;
            if (!$isSiteMgr) {
                $domainSettings = $this->container->get('multi_domain.information');
                $locale = $domainSettings!==null?$domainSettings->getLocale():'en';
                unset($domainSettings);
            } else {
                $mainSettings = $this->container->get('settings');
                $locale = $mainSettings!==null?$mainSettings->getSetting('sitemgr_language'):'en';
                unset($mainSettings);
            }
            $returnValue = $languageHandler->getISOLang($locale);
            unset($locale);
        }
        unset($languageHandler);
        return $returnValue;
    }
}
