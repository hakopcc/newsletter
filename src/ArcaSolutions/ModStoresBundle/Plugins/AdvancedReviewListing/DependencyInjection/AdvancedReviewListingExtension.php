<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AdvancedReviewListingExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('advanced_review_listing.total_reviews_options', $config['total_reviews_options']);
        $container->setParameter('advanced_review_listing.total_review_images', $config['total_review_images']);
        $container->setParameter('advanced_review_listing.review_image_width', $config['review_image_width']);
        $container->setParameter('advanced_review_listing.review_image_height', $config['review_image_height']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/config.yml'));

        if ($config) {
            foreach ($config as $key => $configuration) {
                $container->prependExtensionConfig($key, $configuration);
            }
        }
    }
}
