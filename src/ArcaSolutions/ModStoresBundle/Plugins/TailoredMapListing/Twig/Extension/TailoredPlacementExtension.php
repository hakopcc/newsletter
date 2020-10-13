<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

class TailoredPlacementExtension extends Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var object
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('tailoredPlacement', [$this, 'tailoredPlacement'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * TailoredPlacement function
     *
     * @param Twig_Environment $twig_Environment
     * @return string
     */
    public function tailoredPlacement(Twig_Environment $twig_Environment)
    {
        $tailoredMap = $this->container->get('tailoredplacement.map');
        $parameterHandler = $this->container->get('search.parameters');

        $lat = $this->container->get('settings')->getDomainSetting('default_latitude');
        $long = $this->container->get('settings')->getDomainSetting('default_longitude');
        $zoom = $this->container->get('settings')->getDomainSetting('max_map_zoom');

        $where = implode(' ', $parameterHandler->getWheres());

        $map = $tailoredMap->buildTailoredMap($lat, $long, $zoom, $where);

        return $twig_Environment->render('TailoredMapListingBundle::tailored-placement.html.twig', ['map' => $map]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tailoredPlacement';
    }
}
