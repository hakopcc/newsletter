<?php

namespace ArcaSolutions\WebBundle\Entity;

use ArcaSolutions\ImageBundle\Entity\Image;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * BaseCategory
 * @ORM\MappedSuperclass
 */
class BaseCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"blogDetail", "articleDetail", "listingDetail", "dealDetail", "eventDetail", "classifiedDetail", "API", "ManageCategories"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Serializer\Groups({"blogDetail", "articleDetail", "listingDetail", "dealDetail", "eventDetail", "classifiedDetail", "API", "ManageCategories"})
     */
    protected $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $categoryId;

    /**
     * @var integer
     *
     * @ORM\Column(name="image_id", type="integer", nullable=true)
     */
    protected $imageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="icon_id", type="integer", nullable=true)
     */
    protected $iconId;

    /**
     * @var string
     *
     * @ORM\Column(name="featured", type="string", length=1, nullable=false)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $featured = 'n';

    /**
     * @var string
     *
     * @ORM\Column(name="summary_description", type="string", length=255, nullable=true)
     */
    protected $summaryDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_description", type="string", length=255, nullable=true)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $seoDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="page_title", type="string", length=255, nullable=true)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $pageTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="friendly_url", type="string", length=255, nullable=false)
     * @Serializer\Groups({"blogDetail", "articleDetail", "listingDetail", "dealDetail", "eventDetail", "classifiedDetail", "API", "ManageCategories"})
     */
    protected $friendlyUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="string", length=255, nullable=true)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_keywords", type="string", length=255, nullable=true)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $seoKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $content;

    /**
     * @var string
     *
     * @ORM\Column(name="enabled", type="string", length=1, nullable=false)
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $enabled;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ImageBundle\Entity\Image", fetch="EAGER", cascade={"remove"})
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $image;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ImageBundle\Entity\Image", fetch="EAGER", cascade={"remove"})
     * @ORM\JoinColumn(name="icon_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Groups({"ManageCategories"})
     */
    protected $icon;

    /**
     * @var string
     * @Serializer\Groups({"API", "dealDetail", "Slider"})
     * @Serializer\SerializedName("image_url")
     * @Serializer\Type("string")
     */
    protected $imagePath;

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
     * Set title
     *
     * @param string $title
     * @return BaseCategory
     */
    public function setTitle($title): BaseCategory
    {
        $this->title = $title;

        return $this;
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
     * Set categoryId
     *
     * @param integer $categoryId
     * @return BaseCategory
     */
    public function setCategoryId($categoryId): BaseCategory
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer|null
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set imageId
     *
     * @param integer $imageId
     * @return BaseCategory
     */
    public function setImageId($imageId): BaseCategory
    {
        $this->imageId = $imageId;

        return $this;
    }

    /**
     * Get imageId
     *
     * @return integer|null
     */
    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * Set iconId
     *
     * @param integer $iconId
     * @return BaseCategory
     */
    public function setIconId($iconId): BaseCategory
    {
        $this->iconId = $iconId;

        return $this;
    }

    /**
     * Get iconId
     *
     * @return integer|null
     */
    public function getIconId()
    {
        return $this->iconId;
    }

    /**
     * Set featured
     *
     * @param string $featured
     * @return BaseCategory
     */
    public function setFeatured($featured): BaseCategory
    {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Get featured
     *
     * @return string
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Set summaryDescription
     *
     * @param string $summaryDescription
     * @return BaseCategory
     */
    public function setSummaryDescription($summaryDescription): BaseCategory
    {
        $this->summaryDescription = $summaryDescription;

        return $this;
    }

    /**
     * Get summaryDescription
     *
     * @return string|null
     */
    public function getSummaryDescription()
    {
        return $this->summaryDescription;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     * @return BaseCategory
     */
    public function setSeoDescription($seoDescription): BaseCategory
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * Get seoDescription
     *
     * @return string|null
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * Set pageTitle
     *
     * @param string $pageTitle
     * @return BaseCategory
     */
    public function setPageTitle($pageTitle): BaseCategory
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * Get pageTitle
     *
     * @return string|null
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Set friendlyUrl
     *
     * @param string $friendlyUrl
     * @return BaseCategory
     */
    public function setFriendlyUrl($friendlyUrl): BaseCategory
    {
        $this->friendlyUrl = $friendlyUrl;

        return $this;
    }

    /**
     * Get friendlyUrl
     *
     * @return string
     */
    public function getFriendlyUrl()
    {
        return $this->friendlyUrl;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @return BaseCategory
     */
    public function setKeywords($keywords): BaseCategory
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string|null
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set seoKeywords
     *
     * @param string $seoKeywords
     * @return BaseCategory
     */
    public function setSeoKeywords($seoKeywords): BaseCategory
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    /**
     * Get seoKeywords
     *
     * @return string|null
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return BaseCategory
     */
    public function setContent($content): BaseCategory
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set enabled
     *
     * @param string $enabled
     * @return BaseCategory
     */
    public function setEnabled($enabled): BaseCategory
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return string|null
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Image $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return string|null
     */
    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath($imagePath): void
    {
        $this->imagePath = $imagePath;
    }

    /**
     * @return Image
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param Image $icon
     */
    public function setIcon($icon): void
    {
        $this->icon = $icon;
    }

    /**
     * Get all children categories related
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("children")
     * @Serializer\Groups({"API_WITH_CHILDREN"})
     *
     * @return array
     */
    public function getChildCategories($enabled = false)
    {
        $categories_array = [];

        /* @var $child BaseCategory */
        foreach ($this->getChildren() as $child)
        {
            if ($child->getEnabled() === 'n' && $enabled==true)
                continue;

            $categories_array[] = $child;
        }

        return $categories_array;
    }

    /**
     * Get Children
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param BaseCategory $children
     * @return BaseCategory
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Add children
     *
     * @param ArrayCollection $children
     * @return BaseCategory
     */
    public function addChild(ArrayCollection $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param ArrayCollection $children
     */
    public function removeChild(ArrayCollection $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Set parentId
     *
     * @param BaseCategory $parent
     * @return BaseCategory
     */
    public function setParent(BaseCategory $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get Parent
     *
     * @return BaseCategory
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get an array of all parent ids
     *
     * @param BaseCategory $category
     * @return array
     */
    public function getParentIds($category = null)
    {
        $category = $category ?: $this;

        if ($parent = $category->getParent()) {
            return array_merge([$parent->getId()], $this->getParentIds($parent));
        }

        return [];
    }

    /**
     * @return bool
     */
    public function isLastChild(): bool
    {
        return empty($this->getChildCategories(false));
    }
}
