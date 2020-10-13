<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EnhancedLead\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingEnhancedLead
 *
 * @ORM\Table(name="Listing_EnhancedLead")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ListingEnhancedLead
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
     * @ORM\Column(name="leads_max", type="integer", nullable=true)
     */
    private $leadsMax;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ListingEnhancedLead
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLeadsMax()
    {
        return $this->leadsMax;
    }

    /**
     * @param int $leadsMax
     * @return ListingEnhancedLead
     */
    public function setLeadsMax($leadsMax)
    {
        $this->leadsMax = $leadsMax;

        return $this;
    }
}