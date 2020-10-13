<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * ListingTField
 *
 * @ORM\Table(name="ListingTFieldGroup")
 * @ORM\Entity
 */
class ListingTFieldGroup
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
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTField", mappedBy="listingTFieldGroup")
     */
    private $listingTFields;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget", inversedBy="listingTFieldGroups")
     * @ORM\JoinColumn(name="listingtemplatewidget_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTemplateListingWidget;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingLevelField", mappedBy="listingTFieldGroup")
     */
    private $levels;

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
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return PersistentCollection
     */
    public function getListingTFields()
    {
        return $this->listingTFields;
    }

    /**
     * @param ListingTField[] $listingTFields
     */
    public function setListingTFields($listingTFields): void
    {
        $this->listingTFields = $listingTFields;
    }

    /**
     * @return ListingTemplateListingWidget
     */
    public function getListingTemplateListingWidget(): ListingTemplateListingWidget
    {
        return $this->listingTemplateListingWidget;
    }

    /**
     * @param ListingTemplateListingWidget $listingTemplateListingWidget
     */
    public function setListingTemplateListingWidget($listingTemplateListingWidget): void
    {
        $this->listingTemplateListingWidget = $listingTemplateListingWidget;
    }

    /**
     * @return mixed
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param mixed $levels
     */
    public function setLevels($levels)
    {
        $this->levels = $levels;
    }
}
