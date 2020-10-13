<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MarketSelection\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LocationFeaturedMarket
 *
 * @ORM\Table(name="Location_FeaturedMarket")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\MarketSelection\Repository\MarketSelectionRepository")
 */
class LocationFeaturedMarket
{
    /**
     * @var integer
     *
     * @ORM\Column(name="domain_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $domainId;

    /**
     * @var integer
     *
     * @ORM\Column(name="location_level", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $locationLevel;

    /**
     * @var integer
     *
     * @ORM\Column(name="location_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $locationId;

    /**
     * Get domainId
     *
     * @return integer
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * Set domainId
     *
     * @param integer $domainId
     * @return LocationFeaturedMarket
     */
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;

        return $this;
    }

    /**
     * Get locationLevel
     *
     * @return integer
     */
    public function getLocationLevel()
    {
        return $this->locationLevel;
    }

    /**
     * Set locationLevel
     *
     * @param integer $locationLevel
     * @return LocationFeaturedMarket
     */
    public function setLocationLevel($locationLevel)
    {
        $this->locationLevel = $locationLevel;

        return $this;
    }

    /**
     * Get locationId
     *
     * @return integer
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Set locationId
     *
     * @param integer $locationId
     * @return LocationFeaturedMarket
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;

        return $this;
    }
}
