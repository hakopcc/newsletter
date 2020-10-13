<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdditionalVideosListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingVideos
 *
 * @ORM\Table(name="Listing_Videos")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ListingVideos
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
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\Listing", fetch="EAGER")
     * @ORM\JoinColumn(name="listing_id", referencedColumnName="id")
     */
    private $listing;

    /**
     * @var string
     *
     * @ORM\Column(name="video_snippet", type="text", nullable=true)
     */
    private $video_snippet;

    /**
     * @var string
     *
     * @ORM\Column(name="video_url", type="text", nullable=true)
     */
    private $video_url;

    /**
     * @var string
     *
     * @ORM\Column(name="video_description", type="text", nullable=true)
     */
    private $video_description;

    /**
     * @var string
     *
     * @ORM\Column(name="video_image_url", type="text", nullable=true)
     */
    private $video_image_url;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ListingVideos
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param mixed $listing
     * @return ListingVideos
     */
    public function setListing($listing)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * @return string
     */
    public function getVideoSnippet()
    {
        return $this->video_snippet;
    }

    /**
     * @param string $video_snippet
     * @return ListingVideos
     */
    public function setVideoSnippet($video_snippet)
    {
        $this->video_snippet = $video_snippet;

        return $this;
    }

    /**
     * @return string
     */
    public function getVideoUrl()
    {
        return $this->video_url;
    }

    /**
     * @param string $video_url
     * @return ListingVideos
     */
    public function setVideoUrl($video_url)
    {
        $this->video_url = $video_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getVideoDescription()
    {
        return $this->video_description;
    }

    /**
     * @param string $video_description
     * @return ListingVideos
     */
    public function setVideoDescription($video_description)
    {
        $this->video_description = $video_description;

        return $this;
    }

    /**
     * @return string
     */
    public function getVideoImageUrl()
    {
        return $this->video_image_url;
    }

    /**
     * @param string $video_image_url
     */
    public function setVideoImageUrl(string $video_image_url)
    {
        $this->video_image_url = $video_image_url;
    }
}