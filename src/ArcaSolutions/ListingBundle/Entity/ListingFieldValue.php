<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingFieldValue
 *
 * @ORM\Table(name="ListingFieldValue")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingFieldValueRepository")
 */
class ListingFieldValue
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
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingtfield_id", type="integer", nullable=false)
     */
    private $listingTFieldId;

    /**
     * @var integer
     *
     * @ORM\Column(name="listing_id", type="integer", nullable=false)
     */
    private $listingId;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTField", inversedBy="listingFieldValues", fetch="EAGER")
     * @ORM\JoinColumn(name="listingtfield_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTField;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", inversedBy="fieldsValue")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listing;

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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getListingTFieldId()
    {
        return $this->listingTFieldId;
    }

    /**
     * @param int $listingTFieldId
     */
    public function setListingTFieldId(int $listingTFieldId)
    {
        $this->listingTFieldId = $listingTFieldId;
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
     */
    public function setListingId(int $listingId)
    {
        $this->listingId = $listingId;
    }

    /**
     * @return ListingTField
     */
    public function getListingTField()
    {
        return $this->listingTField;
    }

    /**
     * @param ListingTField $listingTField
     */
    public function setListingTField($listingTField)
    {
        $this->listingTField = $listingTField;
    }

    /**
     * @return Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param Listing $listing
     */
    public function setListing($listing)
    {
        $this->listing = $listing;
    }
}
