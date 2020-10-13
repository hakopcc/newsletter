<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\PersistentCollection;

/**
 * ListingTemplate
 *
 * @ORM\Table(name="ListingTemplate")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingTemplateRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ListingTemplate
{
    const LISTING = 'Listing';

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
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="entered", type="datetime", nullable=false)
     */
    private $entered;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $price = 0.00;
    /**
     * @var string
     *
     * @ORM\Column(name="template_free", type="string", length=20, nullable=false, options={"default"="disabled"})
     */
    private $template_free = 'disabled';

    /**
     * @var integer
     *
     * @ORM\Column(name="summary_template", type="integer", nullable=false)
     */
    private $summaryTemplate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false, options={"default"="enabled"})
     */
    private $status = 'enabled';

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTField", mappedBy="listingTemplate", fetch="EAGER")
     * @ORM\JoinColumn(name="id", referencedColumnName="listingtemplate_id")
     */
    private $fields;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplateTab", mappedBy="listingTemplate", fetch="EAGER")
     * @ORM\JoinColumn(name="id", referencedColumnName="listingtemplate_id")
     */
    private $tabs;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget", mappedBy="listingTemplate")
     */
    private $listingTemplateListingWidgets;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", mappedBy="listingTemplate")
     */
    private $listings;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ImportBundle\Entity\ImportLog", mappedBy="listingTemplate")
     */
    private $importLog;

    /**
     * @ORM\ManyToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingCategory", inversedBy="listingTemplates", cascade={"persist"})
     * @ORM\JoinTable(name="ListingTemplate_ListingCategory",
     *     joinColumns={@JoinColumn(name="listingtemplate_id", referencedColumnName="id", onDelete="cascade")},
     *     inverseJoinColumns={@JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")}
     * )
     * @ORM\OrderBy({"title" = "ASC"})
     */
    private $categories;

    public function __construct() {
        $this->categories = new ArrayCollection();
    }

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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ListingTemplate
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return ListingTemplate
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get entered
     *
     * @return \DateTime
     */
    public function getEntered()
    {
        return $this->entered;
    }

    /**
     * Set entered
     *
     * @param \DateTime $entered
     * @return ListingTemplate
     */
    public function setEntered($entered)
    {
        $this->entered = $entered;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateFree()
    {
        return $this->template_free;
    }

    /**
     * @param string $template_free
     */
    public function setTemplateFree($template_free)
    {
        $this->template_free = $template_free;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return ListingTemplate
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return PersistentCollection|null
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param ListingTField[] $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return PersistentCollection
     */
    public function getListingTemplateListingWidgets()
    {
        return $this->listingTemplateListingWidgets;
    }

    /**
     * @param ListingTemplateListingWidget[] $listingTemplateListingWidgets
     */
    public function setListingTemplateListingWidgets($listingTemplateListingWidgets)
    {
        $this->listingTemplateListingWidgets = $listingTemplateListingWidgets;
    }

    /**
     * @return Listing
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * @param Listing $listings
     */
    public function setListings($listings)
    {
        $this->listings = $listings;
    }

    /**
     * @return ListingTemplateTab[]
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * @param ListingTemplateTab[] $tabs
     */
    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param ListingCategory[] $categories
     */
    public function setCategories($categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @param ListingCategory $category
     */
    public function addCategory(ListingCategory $category)
    {
        $this->categories[] = $category;
    }

    /**
     * @return int
     */
    public function getSummaryTemplate()
    {
        return $this->summaryTemplate;
    }

    /**
     * @param int $summaryTemplate
     */
    public function setSummaryTemplate(int $summaryTemplate)
    {
        $this->summaryTemplate = $summaryTemplate;
    }

    /**
     * @return mixed
     */
    public function getImportLog()
    {
        return $this->importLog;
    }

    /**
     * @param mixed $importLog
     */
    public function setImportLog($importLog)
    {
        $this->importLog = $importLog;
    }

}
