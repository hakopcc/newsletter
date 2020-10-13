<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use Doctrine\ORM\Mapping as ORM;

/**
 * ListingTemplateTab
 *
 * @ORM\Table(name="ListingTemplateTab")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingTemplateTabRepository")
 */
class ListingTemplateTab
{
    const OVERVIEW    = 'Overview';
    const PHOTOS      = 'Photos';
    const REVIEWS     = 'Reviews';
    const DEALS       = 'Deals';
    const CLASSIFIEDS = 'Classifieds';

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
     * @var integer
     *
     * @ORM\Column(name="listingtemplate_id", type="integer", nullable=false)
     */
    private $listingTemplateId;

    /**
     * @var integer
     *
     * @ORM\Column(name="`order`", type="integer", nullable=false)
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplate", inversedBy="tabs")
     * @ORM\JoinColumn(name="listingtemplate_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $listingTemplate;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget", mappedBy="listingTemplateTab")
     */
    private $listingWidgets;

    /**
     * @var boolean
     */
    private $hasContent;

    /**
     * ListingTemplateTab constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        if (!empty($id) && empty($this->id)) {
            $this->id = $id;
        }
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
     * @return ListingTemplateTab
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getListingTemplateId()
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
    public function getListingTemplate()
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
     * @return bool
     */
    public function getHasContent()
    {
        return $this->hasContent;
    }

    /**
     * @param boolean $hasContent
     */
    public function setHasContent($hasContent)
    {
        $this->hasContent = $hasContent;
    }

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
     * @return mixed
     */
    public function getListingWidgets()
    {
        return $this->listingWidgets;
    }

    /**
     * @param mixed $listingWidgets
     */
    public function setListingWidgets($listingWidgets)
    {
        $this->listingWidgets = $listingWidgets;
    }
}
