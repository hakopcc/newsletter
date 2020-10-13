<?php

namespace ArcaSolutions\ListingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingCategory
 *
 * @ORM\Table(name="Linked_Listings")
 * @ORM\Entity
 */
class LinkedListings
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
     * @var Listing
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", inversedBy="linkedListings", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="source_listing", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $sourceListing;

    /**
     * @var Listing
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing")
     * @ORM\JoinColumn(name="linked_listing", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $linkedListing;

    /**
     * @var int
     *
     * @ORM\Column(name="`order`", type="integer", nullable=false)
     */
    protected $order;

    /**
     * @var ListingTField
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTField", inversedBy="linkedListings", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="field", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $field;

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Listing
     */
    public function getSourceListing()
    {
        return $this->sourceListing;
    }

    /**
     * @param Listing $sourceListing
     */
    public function setSourceListing($sourceListing)
    {
        $this->sourceListing = $sourceListing;
    }

    /**
     * @return Listing
     */
    public function getLinkedListing()
    {
        return $this->linkedListing;
    }

    /**
     * @param Listing $linkedListing
     */
    public function setLinkedListing($linkedListing)
    {
        $this->linkedListing = $linkedListing;
    }

    /**
     * @return ListingTField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param ListingTField $field
     */
    public function setField(ListingTField $field)
    {
        $this->field = $field;
    }
}
