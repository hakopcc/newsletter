<?php

namespace ArcaSolutions\ListingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingLevel
 *
 * @ORM\Table(name="ListingLevel", indexes={@ORM\Index(name="active_value", columns={"active", "value"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingLevelRepository")
 */
class ListingLevel
{
    /**
     * Default Listing Levels
     */
    const DIAMOND_LEVEL = 10;
    const GOLD_LEVEL    = 30;
    const SILVER_LEVEL  = 50;
    const BRONZE_LEVEL  = 70;

    /**
     * @var integer
     *
     * @ORM\Column(name="value", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $value = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="defaultlevel", type="string", length=1, nullable=false)
     */
    private $defaultlevel = 'n';

    /**
     * @var string
     *
     * @ORM\Column(name="detail", type="string", length=1, nullable=false)
     */
    private $detail = 'n';

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true, options={"comment":"monthly"})
     */
    private $price = '';

    /**
     * @var string
     *
     * @ORM\Column(name="price_yearly", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $priceYearly = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="free_category", type="integer", nullable=false)
     */
    private $freeCategory = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="category_price", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $categoryPrice = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="active", type="string", length=1, nullable=false)
     */
    private $active = 'y';

    /**
     * @var string
     *
     * @ORM\Column(name="popular", type="string", length=1, nullable=false, options={"default"="n"})
     */
    private $popular = 'n';

    /**
     * @var string
     *
     * @ORM\Column(name="featured", type="string", length=1, nullable=false, options={"default"="n"})
     */
    private $featured = 'n';

    /**
     * @var integer
     *
     * @ORM\Column(name="trial", type="integer", nullable=true)
     */
    private $trial;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingLevelField", mappedBy="listingLevel")
     */
    private $levelFields;

    /**
     * Set value
     *
     * @param integer $value
     * @return ListingLevel
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ListingLevel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set defaultlevel
     *
     * @param string $defaultlevel
     * @return ListingLevel
     */
    public function setDefaultlevel($defaultlevel)
    {
        $this->defaultlevel = $defaultlevel;

        return $this;
    }

    /**
     * Get defaultlevel
     *
     * @return string
     */
    public function getDefaultlevel()
    {
        return $this->defaultlevel;
    }

    /**
     * Set detail
     *
     * @param string $detail
     * @return ListingLevel
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get detail
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return ListingLevel
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
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
     * Set priceYearly
     *
     * @param string $priceYearly
     * @return ListingLevel
     */
    public function setPriceYearly($priceYearly)
    {
        $this->priceYearly = $priceYearly;

        return $this;
    }

    /**
     * Get priceYearly
     *
     * @return string
     */
    public function getPriceYearly()
    {
        return $this->priceYearly;
    }

    /**
     * Set freeCategory
     *
     * @param integer $freeCategory
     * @return ListingLevel
     */
    public function setFreeCategory($freeCategory)
    {
        $this->freeCategory = $freeCategory;

        return $this;
    }

    /**
     * Get freeCategory
     *
     * @return integer
     */
    public function getFreeCategory()
    {
        return $this->freeCategory;
    }

    /**
     * Set categoryPrice
     *
     * @param string $categoryPrice
     * @return ListingLevel
     */
    public function setCategoryPrice($categoryPrice)
    {
        $this->categoryPrice = $categoryPrice;

        return $this;
    }

    /**
     * Get categoryPrice
     *
     * @return string
     */
    public function getCategoryPrice()
    {
        return $this->categoryPrice;
    }

    /**
     * Set active
     *
     * @param string $active
     * @return ListingLevel
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set popular
     *
     * @param string $popular
     * @return ListingLevel
     */
    public function setPopular($popular)
    {
        $this->popular = $popular;

        return $this;
    }

    /**
     * Get popular
     *
     * @return string
     */
    public function getPopular()
    {
        return $this->popular;
    }

    /**
     * Set featured
     *
     * @param string $featured
     * @return ListingLevel
     */
    public function setFeatured($featured)
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
     * Set trial
     *
     * @param integer $trial
     * @return Listinglevel
     */
    public function setTrial($trial)
    {
        $this->trial = $trial;

        return $this;
    }

    /**
     * Get trial
     *
     * @return integer
     */
    public function getTrial()
    {
        return $this->trial;
    }

    /**
     * @return ListingLevelField[]
     */
    public function getLevelFields()
    {
        return $this->levelFields;
    }

    /**
     * @param ListingLevelField[] $levelFields
     */
    public function setLevelFields($levelFields)
    {
        $this->levelFields = $levelFields;
    }

}
