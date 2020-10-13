<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingSocialMedia
 *
 * @ORM\Table(name="Listing_SocialMedia")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ListingSocialMedia
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
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", fetch="EAGER")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id")
     */
    private $listing;

    /**
     * @var integer
     *
     * @ORM\Column(name="import_id", type="integer", nullable=true)
     */
    private $importId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", nullable=true)
     */
    private $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter", type="string", nullable=true)
     */
    private $twitter;

    /**
     * @var string
     *
     * @ORM\Column(name="instagram", type="string", nullable=true)
     */
    private $instagram;

    /**
     * @var string
     *
     * @ORM\Column(name="temp", type="string", nullable=false)
     */
    private $temp;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ListingSocialMedia
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param mixed $listing
     * @return ListingSocialMedia
     */
    public function setListing($listing)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * @return int
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * @param int $importId
     * @return ListingSocialMedia
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     * @return ListingSocialMedia
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     * @return ListingSocialMedia
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstagram()
    {
        return $this->instagram;
    }

    /**
     * @param string $instagram
     * @return ListingSocialMedia
     */
    public function setInstagram($instagram)
    {
        $this->instagram = $instagram;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * @param string $temp
     * @return ListingSocialMedia
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;

        return $this;
    }
}