<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EnhancedLead\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingLevelFieldLeads
 *
 * @ORM\Table(name="ListingLevel_FieldLeads")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ListingLevelFieldLeads
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
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="integer", nullable=false)
     */
    private $field;

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
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return ListingLevelFieldLeads
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set field
     *
     * @param string $field
     * @return ListingLevelFieldLeads
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }
}