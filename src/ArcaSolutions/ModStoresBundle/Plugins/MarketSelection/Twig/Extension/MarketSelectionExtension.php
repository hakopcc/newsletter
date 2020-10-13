<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MarketSelection\Twig\Extension;

use ArcaSolutions\MultiDomainBundle\Services\Settings as MultiDomain;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

class MarketSelectionExtension extends Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var object
     */
    protected $container;

    /**
     * @var MultiDomain
     */
    private $multiDomain;

    /**
     * @param $container
     * @param MultiDomain $multiDomain
     */
    public function __construct(ContainerInterface $container, MultiDomain $multiDomain)
    {
        $this->container = $container;
        $this->multiDomain = $multiDomain;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('market', [$this, 'market'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * MarketSelectionExtension function
     *
     * @param Twig_Environment $twig_Environment
     * @param $content
     *
     * @return string
     */
    public function market(Twig_Environment $twig_Environment)
    {
        $cookies = $this->container->get('request_stack')->getCurrentRequest()->cookies;

        $locationLevel = $this->container->get('settings')->getDomainSetting('show_market');
        if (!$locationLevel) {
            return '';
        }

        $locations = $this->container->get('doctrine')->getRepository('MarketSelectionBundle:LocationFeaturedMarket',
            'main')
            ->getMarketSelection($this->multiDomain->getId(), $locationLevel);

        $selectedLocationLevel = '';
        $selectedLocationId = '';
        $selectedLocationName = '';
        if (
            ($cookies->has('market_location_level') and $level = $cookies->get('market_location_level')) &&
            ($cookies->has('market_location_id') and $id = $cookies->get('market_location_id'))
        ) {
            $currentLocation = $this->container->get('doctrine')->getRepository('CoreBundle:Location'.$level,
                'main')->find($id);

            $selectedLocationLevel = $level;
            $selectedLocationId = $currentLocation->getId();
            $selectedLocationName = $currentLocation->getName();
        }

        return $twig_Environment->render('MarketSelectionBundle::marketselection.html.twig', [
            'locations'             => $locations,
            'selectedLocationName'  => $selectedLocationName,
            'selectedLocationLevel' => $selectedLocationLevel,
            'selectedLocationId'    => $selectedLocationId,
            'locationLevel'         => $locationLevel,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'market';
    }
}