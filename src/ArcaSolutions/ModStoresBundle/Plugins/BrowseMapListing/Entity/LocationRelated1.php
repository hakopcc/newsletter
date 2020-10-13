<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\BrowseMapListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LocationRelated1
 *
 * @ORM\Table(name="LocationRelated_1")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\BrowseMapListing\Repository\LocationRelated1Repository")
 */
class LocationRelated1
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="location_id", type="integer", nullable=false)
     */
    private $locationId;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount_listing", type="integer", nullable=false, options={"default"="0"})
     */
    private $amountListings = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="browsebymap_code", type="string", length=10, nullable=false)
     */
    private $browsebymapCode;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return LocationRelated1
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param int $locationId
     * @return LocationRelated1
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getBrowsebymapCode()
    {
        return $this->browsebymapCode;
    }

    /**
     * @param string $browsebymapCode
     * @return LocationRelated1
     */
    public function setBrowsebymapCode($browsebymapCode)
    {
        $this->browsebymapCode = $browsebymapCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmountListing()
    {
        return $this->amountListing;
    }

    /**
     * @param int $amountListing
     * @return LocationRelated1
     */
    public function setAmountListing($amountListing)
    {
        $this->amountListing = $amountListing;

        return $this;
    }
}
