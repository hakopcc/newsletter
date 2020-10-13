<?php

namespace ArcaSolutions\ListingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * ListingLevelField
 *
 * @ORM\Table(name="ListingLevel_Field", indexes={@ORM\Index(name="theme", columns={"level"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingLevelFieldRepository")
 */
class ListingLevelField
{
    /**
     * Default Listing Level Fields
     */
    const ADDITIONAL_PHONE = 'additionalPhone';
    const ATTACHMENT_FILE  = 'attachmentFile';
    const VIDEO            = 'videoSnippet';
    const PHONE            = 'phone';
    const IMAGES           = 'mainImage';
    const BADGES           = 'choices';
    const SOCIAL_NETWORK   = 'socialNetwork';
    const EMAIL            = 'email';
    const URL              = 'url';
    const DESCRIPTION      = 'description';
    const LONG_DESCRIPTION = 'longDescription';
    const LOCATIONS        = 'locations';
    const FEATURES         = 'features';
    const DEALS            = 'deals';
    const CLASSIFIEDS      = 'classifieds';
    const REVIEW           = 'review';
    const HOURS_WORK       = 'hoursWork';
    const COVER_IMAGE      = 'coverImage';
    const LOGO_IMAGE       = 'logoImage';

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
    private $level = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="string", length=20, nullable=false)
     */
    private $field;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingtfield_id", type="integer", nullable=true)
     */
    private $listingTFieldId;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingtfieldgroup_id", type="integer", nullable=true)
     */
    private $listingTFieldGroupId;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTField", inversedBy="levels")
     * @ORM\JoinColumn(name="listingtfield_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTField;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup", inversedBy="levels")
     * @ORM\JoinColumn(name="listingtfieldgroup_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTFieldGroup;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingLevel", inversedBy="levelFields")
     * @ORM\JoinColumn(name="level", referencedColumnName="value")
     */
    private $listingLevel;

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
     * Set level
     *
     * @param integer $level
     * @return ListingLevelField
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
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
     * Set field
     *
     * @param string $field
     * @return ListingLevelField
     */
    public function setField($field)
    {
        $this->field = $field;

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
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
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
     * @return ListingLevel
     */
    public function getListingLevel()
    {
        return $this->listingLevel;
    }

    /**
     * @param ListingLevel $listingLevel
     */
    public function setListingLevel($listingLevel)
    {
        $this->listingLevel = $listingLevel;
    }

    /**
     * @return ListingTFieldGroup
     */
    public function getListingTFieldGroup()
    {
        return $this->listingTFieldGroup;
    }

    /**
     * @param ListingTFieldGroup $listingTFieldGroup
     */
    public function setListingTFieldGroup($listingTFieldGroup)
    {
        $this->listingTFieldGroup = $listingTFieldGroup;
    }

    /**
     * @return int
     */
    public function getListingTFieldGroupId(): int
    {
        return $this->listingTFieldGroupId;
    }

    /**
     * @param int $listingTFieldGroupId
     */
    public function setListingTFieldGroupId(int $listingTFieldGroupId)
    {
        $this->listingTFieldGroupId = $listingTFieldGroupId;
    }
}
