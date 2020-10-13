<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Review
 *
 * @ORM\Table(name="RatingType")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Repository\RatingTypeRepository")
 */
class RatingType
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
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @var integer
     *
     * @ORM\Column(name="listingtemplate_id", type="integer", nullable=true)
     */
    private $listingTemplateId;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\ReviewRatingType", mappedBy="ratingId")
     * @ORM\JoinColumn(name="id", referencedColumnName="rating_id")
     */
    private $reviews;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return RatingType
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return RatingType
     */
    public function setLabel($label)
    {
        $this->label = $label;

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
     * @return RatingType
     */
    public function setListingTemplateId($listingTemplateId)
    {
        $this->listingTemplateId = $listingTemplateId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param mixed $reviews
     * @return RatingType
     */
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;

        return $this;
    }
}
