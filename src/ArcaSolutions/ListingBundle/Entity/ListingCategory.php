<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingCategory
 *
 * @ORM\Table(name="ListingCategory", indexes={@ORM\Index(name="category_id", columns={"category_id"}), @ORM\Index(name="title1", columns={"title"}), @ORM\Index(name="friendly_url1", columns={"friendly_url"}), @ORM\Index(name="keywords", columns={"keywords", "title"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingCategoryRepository")
 */
class ListingCategory extends BaseCategory
{
    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", mappedBy="categories")
     */
    private $listings;

    /**
     * @var ImportLog
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ImportBundle\Entity\ImportLog", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, name="import_id")
     */
    private $import;

    /**
     * @var ArrayCollection[]
     * @ORM\ManyToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplate", mappedBy="categories")
     */
    private $listingTemplates;

    /**
     * @var ListingCategory
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingCategory", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    protected $children = [];

    /**
     * @var ListingCategory
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingCategory", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    public function __construct() {
        $this->listingTemplates = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * @param mixed $listings
     * @return ListingCategory
     */
    public function setListings($listings)
    {
        $this->listings = $listings;

        return $this;
    }

    /**
     * @return ImportLog
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param ImportLog $import
     */
    public function setImport(ImportLog $import)
    {
        $this->import = $import;
    }

    /**
     * @return ArrayCollection[]
     */
    public function getListingTemplates(): array
    {
        return $this->listingTemplates;
    }

    /**
     * @param ArrayCollection[] $listingTemplates
     */
    public function setListingTemplates(array $listingTemplates): void
    {
        $this->listingTemplates = $listingTemplates;
    }

    /**
     * @param ListingTemplate $listingTemplate
     */
    public function addListingTemplate(ListingTemplate $listingTemplate): void
    {
        $this->listingTemplates[] = $listingTemplate;
    }
}
