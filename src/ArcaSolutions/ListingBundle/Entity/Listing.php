<?php

namespace ArcaSolutions\ListingBundle\Entity;

use ArcaSolutions\DealBundle\Entity\Promotion;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use ArcaSolutions\ImageBundle\Entity\Image;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints\Choice;


/**
 * Listing
 *
 * @ORM\Table(name="Listing", indexes={
 *     @ORM\Index(name="title", columns={"title"}),
 *     @ORM\Index(name="country_id", columns={"location_1"}),
 *     @ORM\Index(name="state_id", columns={"location_2"}),
 *     @ORM\Index(name="region_id", columns={"location_3"}),
 *     @ORM\Index(name="account_id", columns={"account_id"}),
 *     @ORM\Index(name="renewal_date", columns={"renewal_date"}),
 *     @ORM\Index(name="status", columns={"status"}),
 *     @ORM\Index(name="latitude", columns={"latitude"}),
 *     @ORM\Index(name="longitude", columns={"longitude"}),
 *     @ORM\Index(name="level", columns={"level"}),
 *     @ORM\Index(name="city_id", columns={"location_4"}),
 *     @ORM\Index(name="area_id", columns={"location_5"}),
 *     @ORM\Index(name="zip_code", columns={"zip_code"}),
 *     @ORM\Index(name="friendly_url", columns={"friendly_url"}),
 *     @ORM\Index(name="image_id", columns={"image_id"}),
 *     @ORM\Index(name="idx_fulltextsearch_keyword", columns={"fulltextsearch_keyword"}, flags={"fulltext"}),
 *     @ORM\Index(name="idx_fulltextsearch_where", columns={"fulltextsearch_where"}, flags={"fulltext"}),
 *     @ORM\Index(name="updated_date", columns={"updated"}),
 *     @ORM\Index(name="Listing_Promotion", columns={"level", "account_id", "title", "id"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\ListingBundle\Repository\ListingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Listing
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"listingDetail", "Result", "classifiedDetail", "dealDetail", "reviewItem"})
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\Column(name="account_id", type="integer", nullable=true)
     */
    private $accountId;

    /**
     * @var integer
     *
     * @ORM\Column(name="image_id", type="integer", nullable=true)
     */
    private $imageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="cover_id", type="integer", nullable=true)
     */
    private $coverId;

    /**
     * @var integer
     *
     * @ORM\Column(name="logo_id", type="integer", nullable=true)
     */
    private $logoId;

    /**
     * @var integer
     *
     * @ORM\Column(name="location_1", type="integer", nullable=true)
     */
    private $location1 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_2", type="integer", nullable=true)
     */
    private $location2 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_3", type="integer", nullable=true)
     */
    private $location3 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_4", type="integer", nullable=true)
     */
    private $location4 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="location_5", type="integer", nullable=true)
     */
    private $location5 = '0';

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
     * @var \DateTime
     *
     * @ORM\Column(name="renewal_date", type="date", nullable=true)
     */
    private $renewalDate;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_id", type="string", length=10, nullable=true)
     */
    private $discountId;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingtemplate_id", type="integer", nullable=true)
     */
    private $listingTemplateId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Serializer\Groups({"listingDetail", "Result", "classifiedDetail", "dealDetail", "reviewItem"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_title", type="string", length=255, nullable=true)
     */
    private $seoTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="friendly_url", type="string", length=255, nullable=false, unique=true)
     * @Serializer\Groups("listingDetail")
     */
    private $friendlyUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=50, nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="show_email", type="string", nullable=false, options={"default"="y"})
     */
    private $showEmail = 'y';

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="display_url", type="string", length=255, nullable=true)
     */
    private $displayUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=120, nullable=true)
     * @Serializer\Groups({"listingDetail", "Result", "dealDetail", "classifiedDetail"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=120, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=10, nullable=true)
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=50, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=50, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail", "Result"})
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="label_additional_phone", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetailV3", "Result"})
     */
    private $labelAdditionalPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_phone", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetailV3", "Result"})
     */
    private $additionalPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_description", type="string", length=255, nullable=true)
     */
    private $seoDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $longDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="text", nullable=true)
     */
    private $keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_keywords", type="string", length=255, nullable=true)
     */
    private $seoKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment_file", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $attachmentFile;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment_caption", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $attachmentCaption;

    /**
     * @var string
     *
     * @ORM\Column(name="features", type="text", nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $features;

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="social_network", type="json_array", nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $socialNetwork;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=1, nullable=false)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    private $level;

    /**
     * @var boolean
     *
     * @ORM\Column(name="reminder", type="integer", nullable=false, options={"default"="0"})
     */
    private $reminder = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="fulltextsearch_keyword", type="text", nullable=true)
     */
    private $fulltextsearchKeyword;

    /**
     * @var string
     *
     * @ORM\Column(name="fulltextsearch_where", type="text", nullable=true)
     */
    private $fulltextsearchWhere;

    /**
     * @var string
     *
     * @ORM\Column(name="video_snippet", type="text", nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $videoSnippet;

    /**
     * @var string
     *
     * @ORM\Column(name="video_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $videoUrl = '';

    /**
     * @var string
     *
     * @ORM\Column(name="video_description", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $videoDescription;

    /**
     * @var ImportLog
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ImportBundle\Entity\ImportLog", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, name="import_id")
     */
    private $import;

    /**
     * @var string
     *
     * @ORM\Column(name="hours_work", type="text", nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $hoursWork;

    /**
     * @var string
     *
     * @ORM\Column(name="locations", type="text", nullable=true)
     * @Serializer\Groups({"listingDetail"})
     */
    private $locations;

    /**
     * @var string
     *
     * @ORM\Column(name="claim_disable", type="string", length=1, nullable=false, options={"default"="n"})
     */
    private $claimDisable = 'n';

    /**
     * @var integer
     *
     * @ORM\Column(name="number_views", type="integer", nullable=false, options={"default"="0"})
     */
    private $numberViews = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="avg_review", type="integer", nullable=false, options={"default"="0"})
     * @Serializer\Groups({"listingDetail", "Result", "dealDetail", "classifiedDetail"})
     * @Serializer\SerializedName("rating")
     */
    private $avgReview = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="map_zoom", type="integer", nullable=true)
     */
    private $mapZoom;

    /**
     * @var integer
     *
     * @ORM\Column(name="package_id", type="integer", nullable=true)
     */
    private $packageId;

    /**
     * @var string
     *
     * @ORM\Column(name="package_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $packagePrice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_traffic_sent", type="date", nullable=true)
     */
    private $lastTrafficSent;

    /**
     * @var string
     *
     * @ORM\Column(name="custom_id", type="string", length=255, nullable=true)
     */
    private $customId;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ClassifiedBundle\Entity\Classified", mappedBy="listing")
     * @ORM\OrderBy({"status" = "ASC"})
     * @Serializer\Groups({"listingDetail", "Result"})
     * @Serializer\Type("array")
     */
    private $classifieds;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\DealBundle\Entity\Promotion", mappedBy="listing", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="id", referencedColumnName="listing_id")
     * @Serializer\Groups("listingDetail")
     * @Serializer\Type("array")
     */
    private $deals;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingLevel")
     * @ORM\JoinColumn(name="level", referencedColumnName="value")
     */
    private $levelObj;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingChoice", mappedBy="listing")
     * @Serializer\SerializedName("badges")
     * @Serializer\Groups("listingDetail")
     * @Serializer\Type("array")
     */
    private $choices;

    /**
     * @ORM\ManyToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingCategory", inversedBy="listings", cascade={"persist"})
     * @ORM\JoinTable(name="Listing_Category",
     *     joinColumns={@JoinColumn(name="listing_id", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     * @Serializer\Groups({"listingDetail", "dealDetail"})
     * @Serializer\Type("array")
     */
    private $categories;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ImageBundle\Entity\Image", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    private $mainImage;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\WebBundle\Entity\Accountprofilecontact", inversedBy="listings", fetch="EXTRA_LAZY")
     * @JoinColumn(name="account_id", referencedColumnName="account_id", onDelete="CASCADE")
     * @Serializer\Exclude()
     */
    private $account;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ImageBundle\Entity\Image", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="cover_id", referencedColumnName="id")
     */
    private $coverImage;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ImageBundle\Entity\Image", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="logo_id", referencedColumnName="id")
     */
    private $logoImage;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingFieldValue", mappedBy="listing")
     */
    private $fieldsValue;

    /**
     * @var ListingTemplate
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingTemplate", inversedBy="listings", fetch="EXTRA_LAZY")
     * @JoinColumn(name="listingtemplate_id", referencedColumnName="id")
     * @Serializer\Exclude()
     */
    private $listingTemplate;

    /**
     * @Serializer\Groups({"Result", "classifiedDetail", "dealDetail"})
     * @var
     */
    private $imageUrl;

    /**
     * @Serializer\Groups({"listingDetail"})
     * @Serializer\SerializedName("gallery")
     * @var array
     */
    private $galleryAPI;

    /**
     * @Serializer\Groups({"listingDetail"})
     * @var integer
     */
    private $reviewsTotal;

    /**
     * @var string
     * @Serializer\Groups({"Result", "reviewItem"})
     */
    private $type;

    /**
     * @var array
     * @Serializer\Groups({"listingDetail"})
     */
    private $extraFields;

    /**
     * @var integer
     * @Serializer\Groups({"listingDetail", "Result"})
     */
    private $favoriteId;

    /**
     * @var string
     * @Serializer\Groups({"listingDetail"})
     */
    private $detailUrl;

    /**
     * @Serializer\Groups({"listingDetail"})
     */
    private $fax;

    /**
     * @Serializer\Groups({"listingDetailV3"})
     */
    private $logoImageUrl;

    /**
     * @var mixed
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ListingBundle\Entity\LinkedListings", mappedBy="sourceListing")
     */
    private $linkedListings;

    /**
     * Listing constructor.
     */
    public function __construct()
    {
        $this->classifieds = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * Gets triggered on update and insert
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updatedTimestamps()
    {
        $this->updated = new \DateTime();

        if ($this->getEntered() === null) {
            $this->entered = new \DateTime();
        }
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
     *
     * @return Listing
     */
    public function setEntered($entered)
    {
        $this->entered = $entered;

        return $this;
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
     * Get accountId
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Set accountId
     *
     * @param integer $accountId
     *
     * @return Listing
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Get imageId
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * Set imageId
     *
     * @param integer $imageId
     *
     * @return Listing
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;

        return $this;
    }

    /**
     * Get coverId
     *
     * @return integer
     */
    public function getCoverId()
    {
        return $this->coverId;
    }

    /**
     * Set coverId
     *
     * @param integer $coverId
     * @return Listing
     */
    public function setCoverId($coverId)
    {
        $this->coverId = $coverId;

        return $this;
    }

    /**
     * Get logoId
     *
     * @return integer
     */
    public function getLogoId()
    {
        return $this->logoId;
    }

    /**
     * Set logoId
     *
     * @param integer $logoId
     * @return Listing
     */
    public function setLogoId($logoId)
    {
        $this->logoId = $logoId;

        return $this;
    }

    /**
     * Get location1
     *
     * @return integer
     */
    public function getLocation1()
    {
        return $this->location1;
    }

    /**
     * Set location1
     *
     * @param integer $location1
     *
     * @return Listing
     */
    public function setLocation1($location1)
    {
        $this->location1 = $location1;

        return $this;
    }

    /**
     * Get location2
     *
     * @return integer
     */
    public function getLocation2()
    {
        return $this->location2;
    }

    /**
     * Set location2
     *
     * @param integer $location2
     *
     * @return Listing
     */
    public function setLocation2($location2)
    {
        $this->location2 = $location2;

        return $this;
    }

    /**
     * Get location3
     *
     * @return integer
     */
    public function getLocation3()
    {
        return $this->location3;
    }

    /**
     * Set location3
     *
     * @param integer $location3
     *
     * @return Listing
     */
    public function setLocation3($location3)
    {
        $this->location3 = $location3;

        return $this;
    }

    /**
     * Get location4
     *
     * @return integer
     */
    public function getLocation4()
    {
        return $this->location4;
    }

    /**
     * Set location4
     *
     * @param integer $location4
     *
     * @return Listing
     */
    public function setLocation4($location4)
    {
        $this->location4 = $location4;

        return $this;
    }

    /**
     * Get location5
     *
     * @return integer
     */
    public function getLocation5()
    {
        return $this->location5;
    }

    /**
     * Set location5
     *
     * @param integer $location5
     *
     * @return Listing
     */
    public function setLocation5($location5)
    {
        $this->location5 = $location5;

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
     *
     * @return Listing
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get renewalDate
     *
     * @return \DateTime
     */
    public function getRenewalDate()
    {
        return $this->renewalDate;
    }

    /**
     * Set renewalDate
     *
     * @param \DateTime $renewalDate
     *
     * @return Listing
     */
    public function setRenewalDate($renewalDate)
    {
        $this->renewalDate = $renewalDate;

        return $this;
    }

    /**
     * Get discountId
     *
     * @return string
     */
    public function getDiscountId()
    {
        return $this->discountId;
    }

    /**
     * Set discountId
     *
     * @param string $discountId
     *
     * @return Listing
     */
    public function setDiscountId($discountId)
    {
        $this->discountId = $discountId;

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
     * Set title
     *
     * @param string $title
     *
     * @return Listing
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get seoTitle
     *
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * Set seoTitle
     *
     * @param string $seoTitle
     *
     * @return Listing
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;

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
     * Set friendlyUrl
     *
     * @param string $friendlyUrl
     *
     * @return Listing
     */
    public function setFriendlyUrl($friendlyUrl)
    {
        $this->friendlyUrl = $friendlyUrl;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Listing
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get showEmail
     *
     * @return string
     */
    public function getShowEmail()
    {
        return $this->showEmail;
    }

    /**
     * Set showEmail
     *
     * @param string $showEmail
     *
     * @return Listing
     */
    public function setShowEmail($showEmail)
    {
        $this->showEmail = $showEmail;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Listing
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get displayUrl
     *
     * @return string
     */
    public function getDisplayUrl()
    {
        return $this->displayUrl;
    }

    /**
     * Set displayUrl
     *
     * @param string $displayUrl
     *
     * @return Listing
     */
    public function setDisplayUrl($displayUrl)
    {
        $this->displayUrl = $displayUrl;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Listing
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set address2
     *
     * @param string $address2
     *
     * @return Listing
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return Listing
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Listing
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Listing
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Listing
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get label additional phone
     *
     * @return string
     */
    public function getLabelAdditionalPhone()
    {
        return $this->labelAdditionalPhone;
    }

    /**
     * Set label additional phone
     *
     * @param string $labelAdditionalPhone
     *
     * @return Listing
     */
    public function setLabelAdditionalPhone($labelAdditionalPhone)
    {
        $this->labelAdditionalPhone = $labelAdditionalPhone;

        return $this;
    }

    /**
     * Get additional phone
     *
     * @return string
     */
    public function getAdditionalPhone()
    {
        return $this->additionalPhone;
    }

    /**
     * Set additional phone
     *
     * @param string $additionalPhone
     *
     * @return Listing
     */
    public function setAdditionalPhone($additionalPhone)
    {
        $this->additionalPhone = $additionalPhone;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Listing
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get seoDescription
     *
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     *
     * @return Listing
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * Get longDescription
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * Set longDescription
     *
     * @param string $longDescription
     *
     * @return Listing
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     *
     * @return Listing
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get seoKeywords
     *
     * @return string
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * Set seoKeywords
     *
     * @param string $seoKeywords
     *
     * @return Listing
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    /**
     * Get attachmentFile
     *
     * @return string
     */
    public function getAttachmentFile()
    {
        return $this->attachmentFile;
    }

    /**
     * Set attachmentFile
     *
     * @param string $attachmentFile
     *
     * @return Listing
     */
    public function setAttachmentFile($attachmentFile)
    {
        $this->attachmentFile = $attachmentFile;

        return $this;
    }

    /**
     * Get attachmentCaption
     *
     * @return string
     */
    public function getAttachmentCaption()
    {
        return $this->attachmentCaption;
    }

    /**
     * Set attachmentCaption
     *
     * @param string $attachmentCaption
     *
     * @return Listing
     */
    public function setAttachmentCaption($attachmentCaption)
    {
        $this->attachmentCaption = $attachmentCaption;

        return $this;
    }

    /**
     * Get features
     *
     * @return string
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Set features
     *
     * @param string $features
     *
     * @return Listing
     */
    public function setFeatures($features)
    {
        $this->features = $features;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Listing
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Listing
     */
    public function setStatus($status)
    {
        $this->status = $status;

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
     * Set level
     *
     * @param integer $level
     *
     * @return Listing
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get reminder
     *
     * @return boolean
     */
    public function getReminder()
    {
        return $this->reminder;
    }

    /**
     * Set reminder
     *
     * @param boolean $reminder
     *
     * @return Listing
     */
    public function setReminder($reminder)
    {
        $this->reminder = $reminder;

        return $this;
    }

    /**
     * Get fulltextsearchKeyword
     *
     * @return string
     */
    public function getFulltextsearchKeyword()
    {
        return $this->fulltextsearchKeyword;
    }

    /**
     * Set fulltextsearchKeyword
     *
     * @param string $fulltextsearchKeyword
     *
     * @return Listing
     */
    public function setFulltextsearchKeyword($fulltextsearchKeyword)
    {
        $this->fulltextsearchKeyword = $fulltextsearchKeyword;

        return $this;
    }

    /**
     * Get fulltextsearchWhere
     *
     * @return string
     */
    public function getFulltextsearchWhere()
    {
        return $this->fulltextsearchWhere;
    }

    /**
     * Set fulltextsearchWhere
     *
     * @param string $fulltextsearchWhere
     *
     * @return Listing
     */
    public function setFulltextsearchWhere($fulltextsearchWhere)
    {
        $this->fulltextsearchWhere = $fulltextsearchWhere;

        return $this;
    }

    /**
     * Get videoSnippet
     *
     * @return string
     */
    public function getVideoSnippet()
    {
        return $this->videoSnippet;
    }

    /**
     * Set videoSnippet
     *
     * @param string $videoSnippet
     *
     * @return Listing
     */
    public function setVideoSnippet($videoSnippet)
    {
        $this->videoSnippet = $videoSnippet;

        return $this;
    }

    /**
     * Get videoUrl
     *
     * @return string
     */
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    /**
     * Set videoUrl
     *
     * @param string $videoUrl
     *
     * @return Listing
     */
    public function setVideoUrl($videoUrl)
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    /**
     * Get videoDescription
     *
     * @return string
     */
    public function getVideoDescription()
    {
        return $this->videoDescription;
    }

    /**
     * Set videoDescription
     *
     * @param string $videoDescription
     *
     * @return Listing
     */
    public function setVideoDescription($videoDescription)
    {
        $this->videoDescription = $videoDescription;

        return $this;
    }

    /**
     * Get import
     *
     * @return ImportLog
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * Set import
     *
     * @param ImportLog $import
     *
     * @return Listing
     */
    public function setImport(ImportLog $import)
    {
        $this->import = $import;

        return $this;
    }

    /**
     * Get hoursWork
     *
     * @return string
     */
    public function getHoursWork()
    {
        return $this->hoursWork;
    }

    /**
     * Set hoursWork
     *
     * @param string $hoursWork
     *
     * @return Listing
     */
    public function setHoursWork($hoursWork)
    {
        $this->hoursWork = $hoursWork;

        return $this;
    }

    /**
     * Get locations
     *
     * @return string
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * Set locations
     *
     * @param string $locations
     *
     * @return Listing
     */
    public function setLocations($locations)
    {
        $this->locations = $locations;

        return $this;
    }

    /**
     * Get claimDisable
     *
     * @return string
     */
    public function getClaimDisable()
    {
        return $this->claimDisable;
    }

    /**
     * Set claimDisable
     *
     * @param string $claimDisable
     *
     * @return Listing
     */
    public function setClaimDisable($claimDisable)
    {
        $this->claimDisable = $claimDisable;

        return $this;
    }

    /**
     * Get numberViews
     *
     * @return integer
     */
    public function getNumberViews()
    {
        return $this->numberViews;
    }

    /**
     * Set numberViews
     *
     * @param integer $numberViews
     *
     * @return Listing
     */
    public function setNumberViews($numberViews)
    {
        $this->numberViews = $numberViews;

        return $this;
    }

    /**
     * Get avgReview
     *
     * @return integer
     */
    public function getAvgReview()
    {
        return $this->avgReview;
    }

    /**
     * Set avgReview
     *
     * @param integer $avgReview
     *
     * @return Listing
     */
    public function setAvgReview($avgReview)
    {
        $this->avgReview = $avgReview;

        return $this;
    }

    /**
     * Get mapZoom
     *
     * @return integer
     */
    public function getMapZoom()
    {
        return $this->mapZoom;
    }

    /**
     * Set mapZoom
     *
     * @param integer $mapZoom
     *
     * @return Listing
     */
    public function setMapZoom($mapZoom)
    {
        $this->mapZoom = $mapZoom;

        return $this;
    }

    /**
     * Get packageId
     *
     * @return integer
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * Set packageId
     *
     * @param integer $packageId
     *
     * @return Listing
     */
    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;

        return $this;
    }

    /**
     * Get packagePrice
     *
     * @return string
     */
    public function getPackagePrice()
    {
        return $this->packagePrice;
    }

    /**
     * Set packagePrice
     *
     * @param string $packagePrice
     *
     * @return Listing
     */
    public function setPackagePrice($packagePrice)
    {
        $this->packagePrice = $packagePrice;

        return $this;
    }

    /**
     * Get lastTrafficSent
     *
     * @return \DateTime
     */
    public function getLastTrafficSent()
    {
        return $this->lastTrafficSent;
    }

    /**
     * Set lastTrafficSent
     *
     * @param \DateTime $lastTrafficSent
     *
     * @return Listing
     */
    public function setLastTrafficSent($lastTrafficSent)
    {
        $this->lastTrafficSent = $lastTrafficSent;

        return $this;
    }

    /**
     * Get customId
     *
     * @return string
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * Set customId
     *
     * @param string $customId
     *
     * @return Listing
     */
    public function setCustomId($customId)
    {
        $this->customId = $customId;

        return $this;
    }

    /**
     * @return Promotion[]
     */
    public function getDeals()
    {
        return $this->deals;
    }

    /**
     * @param Promotion[]|ArrayCollection $deals
     *
     * @return Listing
     */
    public function setDeals($deals)
    {
        $this->deals = $deals;

        return $this;
    }

    /**
     * @return Choice[]
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param Choice[] $choices
     *
     * @return Listing
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param ArrayCollection $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return Image
     */
    public function getMainImage()
    {
        return $this->mainImage;
    }

    /**
     * @param Image $mainImage
     */
    public function setMainImage($mainImage)
    {
        $this->mainImage = $mainImage;
    }

    /**
     * @return Accountprofilecontact
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Accountprofilecontact $account
     */
    public function setAccount(Accountprofilecontact $account = null)
    {
        $this->account = $account;
    }

    /**
     * @return Image
     */
    public function getCoverImage()
    {
        return $this->coverImage;
    }

    /**
     * @param Image $coverImage
     *
     * @return Listing
     */
    public function setCoverImage($coverImage)
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    /**
     * @return Image
     */
    public function getLogoImage()
    {
        return $this->logoImage;
    }

    /**
     * @param Image $logoImage
     */
    public function setLogoImage($logoImage)
    {
        $this->logoImage = $logoImage;
    }

    /**
     * @return ListingLevel
     */
    public function getLevelObj()
    {
        return $this->levelObj;
    }

    /**
     * @param ListingLevel $levelObj
     *
     * @return $this
     */
    public function setLevelObj($levelObj)
    {
        $this->levelObj = $levelObj;

        return $this;
    }

    /**
     * @return string
     */
    public function getSocialNetwork()
    {
        return $this->socialNetwork;
    }

    /**
     * @param string $socialNetwork
     *
     * @return Listing
     */
    public function setSocialNetwork($socialNetwork)
    {
        $this->socialNetwork = $socialNetwork;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getClassifieds()
    {
        return $this->classifieds;
    }

    /**
     * @param ArrayCollection $classifieds
     * @return Listing
     */
    public function setClassifieds($classifieds)
    {
        $this->classifieds = $classifieds;

        return $this;
    }

    /**
     * @param bool $setNull
     * @return $this
     */
    public function cleanClassifieds($setNull = false)
    {
        $setNull ? $this->classifieds = null : $this->classifieds->clear();

        return $this;
    }

    /**
     * @return array
     */
    public function getGalleryAPI()
    {
        return $this->galleryAPI;
    }

    /**
     * @param array $galleryAPI
     */
    public function setGalleryAPI($galleryAPI)
    {
        $this->galleryAPI = $galleryAPI;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @param mixed $reviewsTotal
     */
    public function setReviewsTotal($reviewsTotal)
    {
        $this->reviewsTotal = $reviewsTotal;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getExtraFields()
    {
        return $this->extraFields;
    }

    /**
     * @param array $extraFields
     */
    public function setExtraFields($extraFields)
    {
        $this->extraFields = $extraFields;
    }

    /**
     * @return int
     */
    public function getFavoriteId()
    {
        return $this->favoriteId;
    }

    /**
     * @param int $favoriteId
     */
    public function setFavoriteId($favoriteId)
    {
        $this->favoriteId = $favoriteId;
    }

    /**
     * @return string
     */
    public function getDetailUrl()
    {
        return $this->detailUrl;
    }

    /**
     * @param string $detailUrl
     */
    public function setDetailUrl($detailUrl)
    {
        $this->detailUrl = $detailUrl;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return mixed
     */
    public function getLogoImageUrl()
    {
        return $this->logoImageUrl;
    }

    /**
     * @param mixed $logoImageUrl
     */
    public function setLogoImageUrl($logoImageUrl)
    {
        $this->logoImageUrl = $logoImageUrl;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("geo")
     * @Serializer\Groups({"listingDetail", "Result"})
     *
     * @return array
     */
    public function getGeoLocation()
    {
        $geoLocation = null;

        if ($this->latitude || $this->longitude) {
            $geoLocation = [
                'lat' => (double)$this->latitude,
                'lng' => (double)$this->longitude,
            ];
        }

        return $geoLocation;
    }

    /**
     * Gets triggered on update and insert
     *
     * @ORM\PrePersist()
     */
    public function persistTimestamps()
    {
        $this->lastTrafficSent = new \DateTime('now');
    }

    /**
     * @return PersistentCollection
     */
    public function getFieldsValue()
    {
        return $this->fieldsValue;
    }

    /**
     * @param ListingFieldValue[] $fieldsValue
     */
    public function setFieldsValue($fieldsValue)
    {
        $this->fieldsValue = $fieldsValue;
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
     * @return Listing
     */
    public function setListingTemplate(ListingTemplate $listingTemplate)
    {
        $this->listingTemplate = $listingTemplate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLinkedListings()
    {
        return $this->linkedListings;
    }

    /**
     * @param mixed $linkedListings
     */
    public function setLinkedListings($linkedListings)
    {
        $this->linkedListings = $linkedListings;
    }
}
