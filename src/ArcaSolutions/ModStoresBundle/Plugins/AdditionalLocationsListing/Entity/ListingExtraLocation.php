<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdditionalLocationsListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingExtraLocation
 *
 * @ORM\Table(name="Listing_ExtraLocation")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ListingExtraLocation
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
     * @ORM\Column(name="listing_id", type="integer", nullable=false)
     */
    private $listingId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="loc_location_1", type="integer", nullable=false, options={"default"=0})
     */
    private $location1 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="loc_location_2", type="integer", nullable=false, options={"default"=0})
     */
    private $location2 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="loc_location_3", type="integer", nullable=false, options={"default"=0})
     */
    private $location3 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="loc_location_4", type="integer", nullable=false, options={"default"=0})
     */
    private $location4 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="loc_location_5", type="integer", nullable=false, options={"default"=0})
     */
    private $location5 = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="loc_address", type="string", length=50, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_address2", type="string", length=50, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_zip_code", type="string", length=10, nullable=true)
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_zip5", type="string", length=10, nullable=true)
     */
    private $zip5 = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="loc_latitude", type="string", length=50, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_longitude", type="string", length=50, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_map_tuning", type="string", length=255, nullable=true)
     */
    private $maptuning;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_map_zoom", type="integer", nullable=true)
     */
    private $mapzoom;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ListingExtraLocation
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getListingId()
    {
        return $this->listingId;
    }

    /**
     * @param int $listingId
     * @return ListingExtraLocation
     */
    public function setListingId($listingId)
    {
        $this->listingId = $listingId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocation1()
    {
        return $this->location1;
    }

    /**
     * @param int $location1
     * @return ListingExtraLocation
     */
    public function setLocation1($location1)
    {
        $this->location1 = $location1;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocation2()
    {
        return $this->location2;
    }

    /**
     * @param int $location2
     * @return ListingExtraLocation
     */
    public function setLocation2($location2)
    {
        $this->location2 = $location2;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocation3()
    {
        return $this->location3;
    }

    /**
     * @param int $location3
     * @return ListingExtraLocation
     */
    public function setLocation3($location3)
    {
        $this->location3 = $location3;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocation4()
    {
        return $this->location4;
    }

    /**
     * @param int $location4
     * @return ListingExtraLocation
     */
    public function setLocation4($location4)
    {
        $this->location4 = $location4;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocation5()
    {
        return $this->location5;
    }

    /**
     * @param int $location5
     * @return ListingExtraLocation
     */
    public function setLocation5($location5)
    {
        $this->location5 = $location5;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return ListingExtraLocation
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     * @return ListingExtraLocation
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     * @return ListingExtraLocation
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getZip5()
    {
        return $this->zip5;
    }

    /**
     * @param string $zip5
     * @return ListingExtraLocation
     */
    public function setZip5($zip5)
    {
        $this->zip5 = $zip5;

        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return ListingExtraLocation
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return ListingExtraLocation
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getMaptuning()
    {
        return $this->maptuning;
    }

    /**
     * @param string $maptuning
     * @return ListingExtraLocation
     */
    public function setMaptuning($maptuning)
    {
        $this->maptuning = $maptuning;

        return $this;
    }

    /**
     * @return string
     */
    public function getMapzoom()
    {
        return $this->mapzoom;
    }

    /**
     * @param string $mapzoom
     * @return ListingExtraLocation
     */
    public function setMapzoom($mapzoom)
    {
        $this->mapzoom = $mapzoom;

        return $this;
    }
}