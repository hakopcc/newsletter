<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingLevelService
 * @package ArcaSolutions\ListingBundle\Services
 */
class ListingLevelService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ListingLevelService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    const PRICE_PERIOD_MONTHLY = 'Monthly';
    const PRICE_PERIOD_YEARLY = 'Yearly';

    /**
     * Get price of listing level. Returns null if informed renewal period is invalid
     * @param ListingLevel $srcListingLevel
     * @param string $renewalPeriod
     * @return float|null
     */
    public function getPrice(ListingLevel $srcListingLevel, $renewalPeriod = '')
    {
        $returnValue = null;
        if(empty($renewalPeriod) || in_array($renewalPeriod, array($this::PRICE_PERIOD_MONTHLY, $this::PRICE_PERIOD_YEARLY), true)) {
            $returnValue = (float)(($renewalPeriod===$this::PRICE_PERIOD_YEARLY) ? $srcListingLevel->getPriceYearly() : $srcListingLevel->getPrice());
        }
        return $returnValue;
    }

    /**
     * @return ListingLevel[]
     */
    public function getAllListingLevels()
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevel')->findBy([
            'active' => 'y'
        ]);
    }
}
