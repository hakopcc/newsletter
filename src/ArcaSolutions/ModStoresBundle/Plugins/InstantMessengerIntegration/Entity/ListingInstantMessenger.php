<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingInstantMessenger.
 *
 * @ORM\Table(name="Listing_InstantMessenger")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Repository\ListingInstantMessengerRepository")
 */
class ListingInstantMessenger
{
    /**
     * @var int
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
     * @var int
     *
     * @ORM\Column(name="import_id", type="integer", nullable=true)
     */
    private $importId;

    /**
     * @var string
     *
     * @ORM\Column(name="temp", type="string", nullable=false, options={"default" : "n"})
     */
    private $temp = 'n';

    /**
     * @var string
     *
     * @ORM\Column(name="instant_messenger", type="json_array", nullable=true)
     */
    private $instantMessenger;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ListingInstantMessenger
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
     *
     * @return ListingInstantMessenger
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
     *
     * @return ListingInstantMessenger
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstantMessenger()
    {
        return $this->instantMessenger;
    }

    /**
     * @param string $instantMessenger
     *
     * @return ListingInstantMessenger
     */
    public function setInstantMessenger($instantMessenger)
    {
        $this->instantMessenger = $instantMessenger;

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
     *
     * @return ListingInstantMessenger
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;

        return $this;
    }
}
