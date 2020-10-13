<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * ListingTField
 *
 * @ORM\Table(name="ListingTField")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingTFieldRepository")
 */
class ListingTField
{
    /**
     * Listing Template Fields Type
     */
    const DEFAULT_TYPE = 'default';

    /**
     * Listing Template Default Fields
     */
    const ADDITIONAL_PHONE = 'Additional Phone';
    const ATTACHMENT_FILE  = 'Attachment File';
    const VIDEO            = 'Video';
    const PHONE            = 'Phone';
    const IMAGES           = 'Images';
    const BADGES           = 'Badges';
    const SOCIAL_NETWORK   = 'Social Network';
    const EMAIL            = 'E-mail';
    const URL              = 'Url';
    const DESCRIPTION      = 'Summary Description';
    const LONG_DESCRIPTION = 'Long Description';
    const LOCATIONS        = 'Locations';
    const FEATURES         = 'Features';
    const DEALS            = 'Deals';
    const CLASSIFIEDS      = 'Classifieds';
    const REVIEW           = 'Review';
    const HOURS_WORK       = 'Hours Work';
    const COVER_IMAGE      = 'Cover Image';
    const LOGO_IMAGE       = 'Logo Image';

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
     * @ORM\Column(name="field_type", type="string", length=255, nullable=false)
     */
    private $fieldType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="required", type="boolean", length=1, nullable=false, options={"default"="0"})
     */
    private $required = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="placeholder", type="string", length=255, nullable=true)
     */
    private $placeholder;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingtemplate_id", type="integer", nullable=false)
     */
    private $listingTemplateId;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer", nullable=true)
     */
    private $groupId;

    /**
     * @var string
     *
     * @ORM\Column(name="attributes", type="json", nullable=true)
     */
    private $attributes;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplate", inversedBy="fields")
     * @ORM\JoinColumn(name="listingtemplate_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTemplate;

    /**
     * @var ListingTemplateListingWidget[]
     * @ORM\ManyToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget", mappedBy="listingTFields", cascade={"persist"})
     */
    private $listingWidgets;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingLevelField", mappedBy="listingTField")
     */
    private $levels;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingFieldValue", mappedBy="listingTField")
     */
    private $listingFieldValues;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup", inversedBy="listingTFields")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTFieldGroup;

    public function __construct() {
        $this->listingWidgets = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    /**
     * @param string $fieldType
     */
    public function setFieldType(string $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required)
    {
        $this->required = $required;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getListingTemplateId(): int
    {
        return $this->listingTemplateId;
    }

    /**
     * @param int $listingTemplateId
     */
    public function setListingTemplateId(int $listingTemplateId)
    {
        $this->listingTemplateId = $listingTemplateId;
    }

    /**
     * @return ListingTemplate
     */
    public function getListingTemplate(): ListingTemplate
    {
        return $this->listingTemplate;
    }

    /**
     * @param ListingTemplate $listingTemplate
     */
    public function setListingTemplate($listingTemplate)
    {
        $this->listingTemplate = $listingTemplate;
    }

    /**
     * @return ArrayCollection
     */
    public function getListingWidgets()
    {
        return $this->listingWidgets;
    }

    /**
     * @param ListingTemplateListingWidget[] $listingWidgets
     */
    public function setListingWidgets(array $listingWidgets)
    {
        $this->listingWidgets = $listingWidgets;
    }

    /**
     * @param ListingTemplateListingWidget $listingWidget
     */
    public function addListingWidget(ListingTemplateListingWidget $listingWidget)
    {
        $this->listingWidgets[] = $listingWidget;
    }

    /**
     * @param ListingTemplateListingWidget $listingWidget
     */
    public function removeListingWidget(ListingTemplateListingWidget $listingWidget)
    {
        if (!$this->listingWidgets->contains($listingWidget)) {
            return;
        }
        $this->listingWidgets->removeElement($listingWidget);
        $listingWidget->removeListingTField($this);
    }

    /**
     * @return PersistentCollection
     */
    public function getListingFieldValues()
    {
        return $this->listingFieldValues;
    }

    /**
     * @param ListingFieldValue $listingFieldValues
     */
    public function setListingFieldValues($listingFieldValues): void
    {
        $this->listingFieldValues = $listingFieldValues;
    }

    /**
     * @return ListingTFieldGroup|null
     */
    public function getListingTFieldGroup()
    {
        return $this->listingTFieldGroup;
    }

    /**
     * @param ListingTFieldGroup $listingTFieldGroup
     */
    public function setListingTFieldGroup($listingTFieldGroup): void
    {
        $this->listingTFieldGroup = $listingTFieldGroup;
    }

    /**
     * @return PersistentCollection|null
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param ListingLevelField[] $levels
     */
    public function setLevels($levels): void
    {
        $this->levels = $levels;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $attributes
     */
    public function setAttributes(string $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId(int $groupId)
    {
        $this->groupId = $groupId;
    }
}
