<?php

namespace ArcaSolutions\ImageBundle\EventListener;

use ArcaSolutions\ImageBundle\Entity\Image;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImageListener
 * @package App\EventListener
 */
class ImageListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $imagePath;

    /**
     * ImageListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Image $image
     */
    public function preRemove(Image $image): void
    {
        $this->container->get('utility')->setPackages();

        $this->imagePath = $this->container->get('templating.helper.assets')
            ->getUrl($this->container->get('imagehandler')->getPath($image), 'domain_images');
    }

    /**
     * @param Image $image
     */
    public function postRemove(Image $image): void
    {
        $s = DIRECTORY_SEPARATOR;
        $fullImagePath = $this->container->get('kernel')->getRootDir() . "{$s}..{$s}web" . $this->imagePath;
        if(file_exists($fullImagePath)) {
            @unlink($fullImagePath);
        }
    }
}
