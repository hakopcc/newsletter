<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tailored_map_listing');

        $rootNode
            ->children()
            ->integerNode('pin_width')->end()
            ->integerNode('pin_height')->end()
            ->floatNode('default_lattitude')->end()
            ->floatNode('default_longtide')->end()
            ->integerNode('default_zoom')->end()
            ->integerNode('max_pins_map')->end()
            ->end();

        return $treeBuilder;
    }
}
