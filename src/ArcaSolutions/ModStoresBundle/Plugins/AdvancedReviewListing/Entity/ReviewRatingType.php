<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Review
 *
 * @ORM\Table(name="Review_RatingType")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Repository\ReviewRatingTypeRepository")
 */
class ReviewRatingType
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
     * @ORM\Column(name="review_id", type="integer", nullable=false)
     */
    private $reviewId;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Entity\RatingType", inversedBy="reviews")
     * @ORM\JoinColumn(name="rating_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ratingId;

    /**
     * @var integer
     *
     * @ORM\Column(name="value", type="integer", nullable=false)
     */
    private $value = '0';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ReviewRatingType
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getReviewId()
    {
        return $this->reviewId;
    }

    /**
     * @param int $reviewId
     * @return ReviewRatingType
     */
    public function setReviewId($reviewId)
    {
        $this->reviewId = $reviewId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRatingId()
    {
        return $this->ratingId;
    }

    /**
     * @param mixed $ratingId
     * @return ReviewRatingType
     */
    public function setRatingId($ratingId)
    {
        $this->ratingId = $ratingId;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return ReviewRatingType
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
