<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\GeoTargetedBanner\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Example
 *
 * @ORM\Table(name="Banner_GeoTargeted")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\GeoTargetedBanner\Repository\BannerGeoTargetedRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BannerGeoTargeted
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
     * @ORM\Column(name="location_1", type="integer", nullable=true)
     */
    private $location1 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_2", type="integer", nullable=true)
     */
    private $location2 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_3", type="integer", nullable=true)
     */
    private $location3 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_4", type="integer", nullable=true)
     */
    private $location4 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_5", type="integer", nullable=true)
     */
    private $location5 = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=50, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=50, nullable=true)
     */
    private $longitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="map_zoom", type="integer", nullable=true)
     */
    private $mapZoom = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="distance", type="integer", nullable=true)
     */
    private $distance;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\BannersBundle\Entity\Banner", fetch="EAGER")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $banner;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get location1
     *
     * @return integer
     */
    public function getLocation1()
    {
        return $this->location1;
    }

    /**
     * Set location1
     *
     * @param integer $location1
     *
     * @return BannerGeoTargeted
     */
    public function setLocation1($location1)
    {
        $this->location1 = $location1;

        return $this;
    }

    /**
     * Get location2
     *
     * @return integer
     */
    public function getLocation2()
    {
        return $this->location2;
    }

    /**
     * Set location2
     *
     * @param integer $location2
     *
     * @return BannerGeoTargeted
     */
    public function setLocation2($location2)
    {
        $this->location2 = $location2;

        return $this;
    }

    /**
     * Get location3
     *
     * @return integer
     */
    public function getLocation3()
    {
        return $this->location3;
    }

    /**
     * Set location3
     *
     * @param integer $location3
     *
     * @return BannerGeoTargeted
     */
    public function setLocation3($location3)
    {
        $this->location3 = $location3;

        return $this;
    }

    /**
     * Get location4
     *
     * @return integer
     */
    public function getLocation4()
    {
        return $this->location4;
    }

    /**
     * Set location4
     *
     * @param integer $location4
     *
     * @return BannerGeoTargeted
     */
    public function setLocation4($location4)
    {
        $this->location4 = $location4;

        return $this;
    }

    /**
     * Get location5
     *
     * @return integer
     */
    public function getLocation5()
    {
        return $this->location5;
    }

    /**
     * Set location5
     *
     * @param integer $location5
     *
     * @return BannerGeoTargeted
     */
    public function setLocation5($location5)
    {
        $this->location5 = $location5;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return BannerGeoTargeted
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return BannerGeoTargeted
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get mapZoom
     *
     * @return integer
     */
    public function getMapZoom()
    {
        return $this->mapZoom;
    }

    /**
     * Set mapZoom
     *
     * @param integer
     *
     * @return BannerGeoTargeted
     */
    public function setMapZoom($mapZoom)
    {
        $this->location5 = $mapZoom;

        return $this;
    }

    /**
     * Get distance
     *
     * @return integer
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set distance
     *
     * @param integer $distance
     * @return BannerGeoTargeted
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param mixed $banner
     * @return BannerGeoTargeted
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;

        return $this;
    }
}