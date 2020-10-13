<?php


namespace ArcaSolutions\WysiwygBundle\Entity;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\PersistentCollection;

/**
 * ListingTemplateListingWidget
 *
 * @ORM\Table(name="ListingTemplate_ListingWidget")
 * @ORM\Entity(repositoryClass="ArcaSolutions\WysiwygBundle\Repository\ListingTemplateListingWidgetRepository")
 */
class ListingTemplateListingWidget
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
     * @ORM\Column(name="listingtemplate_id", type="integer", nullable=false)
     */
    private $listingTemplateId;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingwidget_id", type="integer", nullable=false)
     */
    private $listingWidgetId;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingtemplatetab_id", type="integer", nullable=true)
     */
    private $listingTemplateTabId;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var integer
     *
     * @ORM\Column(name="`order`", type="integer", nullable=false)
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplate", inversedBy="listingTemplateListingWidgets", fetch="EAGER")
     * @ORM\JoinColumn(name="listingtemplate_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTemplate;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\WysiwygBundle\Entity\ListingWidget")
     * @ORM\JoinColumn(name="listingwidget_id", referencedColumnName="id")
     */
    private $listingWidget;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplateTab", inversedBy="listingWidgets")
     * @ORM\JoinColumn(name="listingtemplatetab_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTemplateTab;

    /**
     * @ORM\ManyToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTField", inversedBy="listingWidgets", cascade={"persist"})
     * @ORM\JoinTable(name="ListingTField_ListingTemplateWidget",
     *     joinColumns={@JoinColumn(name="listingtemplatewidget_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@JoinColumn(name="listingtfield_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $listingTFields;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup", mappedBy="listingTemplateListingWidget")
     */
    private $listingTFieldGroups;

    public function __construct() {
        $this->listingTFields = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
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
     * @return int
     */
    public function getListingWidgetId(): int
    {
        return $this->listingWidgetId;
    }

    /**
     * @param int $listingWidgetId
     */
    public function setListingWidgetId(int $listingWidgetId)
    {
        $this->listingWidgetId = $listingWidgetId;
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
     * @return ListingWidget
     */
    public function getListingWidget(): ListingWidget
    {
        return $this->listingWidget;
    }

    /**
     * @param ListingWidget $listingWidget
     */
    public function setListingWidget($listingWidget)
    {
        $this->listingWidget = $listingWidget;
    }

    /**
     * @return int
     */
    public function getListingTemplateTabId()
    {
        return $this->listingTemplateTabId;
    }

    /**
     * @param int $listingTemplateTabId
     */
    public function setListingTemplateTabId(int $listingTemplateTabId)
    {
        $this->listingTemplateTabId = $listingTemplateTabId;
    }

    /**
     * @return ListingTemplateTab|null
     */
    public function getListingTemplateTab()
    {
        return $this->listingTemplateTab;
    }

    /**
     * @param ListingTemplateTab $listingTemplateTab
     */
    public function setListingTemplateTab($listingTemplateTab)
    {
        $this->listingTemplateTab = $listingTemplateTab;
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
     * @param ListingTField $listingTField
     */
    public function addListingTField(ListingTField $listingTField)
    {
        $this->listingTFields[] = $listingTField;
    }

    /**
     * @param ListingTField $listingTField
     */
    public function removeListingTField(ListingTField $listingTField)
    {
        if (!$this->listingTFields->contains($listingTField)) {
            return;
        }
        $this->listingTFields->removeElement($listingTField);
        $listingTField->removeListingWidget($this);
    }

    /**
     * @return PersistentCollection
     */
    public function getListingTFieldGroups()
    {
        return $this->listingTFieldGroups;
    }

    /**
     * @param ListingTFieldGroup[] $listingTFieldGroups
     */
    public function setListingTFieldGroups($listingTFieldGroups): void
    {
        $this->listingTFieldGroups = $listingTFieldGroups;
    }
}
