<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\DependencyInjection;

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
        $rootNode = $treeBuilder->root('advanced_review_listing');

        $rootNode
            ->children()
            ->integerNode('total_reviews_options')->end()
            ->integerNode('total_review_images')->end()
            ->integerNode('review_image_width')->end()
            ->integerNode('review_image_height')->end()
            ->end();

        return $treeBuilder;
    }
}
