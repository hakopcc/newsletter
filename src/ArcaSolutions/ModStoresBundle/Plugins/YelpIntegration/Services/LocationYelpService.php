<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Services;


use Symfony\Component\DependencyInjection\Container;
use Throwable;


class LocationYelpService
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Works for strings like L1:4 L3:104 L4:14200
     *
     * @param string $elasticLocations
     * @return array|bool
     * @throws Throwable
     */
    public function convertElasticStringToObjects($elasticLocations)
    {
        if (!is_string($elasticLocations)) {
            return false;
        }

        $locations = [];
        $locationsObj = [];
        $locationsAux = array_reverse(explode(' ', $elasticLocations));
        foreach ($locationsAux as $location) {
            $locationAux = explode(':', $location);
            $locationLevel = substr($locationAux[0], 1, strlen($locationAux[0]));
            $locations[$locationLevel] = $locationAux[1];
        }

        foreach ($locations as $level => $id) {
            $locationsObj[] = $this->container->get('doctrine')->getRepository("CoreBundle:Location$level",
                'main')->findOneBy(['id' => $id]);
        }

        return $locationsObj;
    }
}