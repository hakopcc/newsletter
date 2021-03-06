<?php

namespace ArcaSolutions\ImageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Image
 *
 * @ORM\Table(name="Image")
 * @ORM\Entity
 * @ORM\EntityListeners({"ArcaSolutions\ImageBundle\EventListener\ImageListener"})
 */
class Image
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"listingDetail", "eventDetail", "ManageCategories"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     * @Serializer\Groups({"listingDetail", "eventDetail"})
     */
    private $type = 'JPG';

    /**
     * @var integer
     *
     * @ORM\Column(name="width", type="smallint", nullable=true)
     * @Serializer\Groups({"listingDetail", "eventDetail"})
     */
    private $width = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="smallint", nullable=true)
     * @Serializer\Groups({"listingDetail", "eventDetail"})
     */
    private $height = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail", "eventDetail"})
     */
    private $prefix;

    /**
     * @var string
     *
     * @ORM\Column(name="unsplash", type="string", length=255, nullable=true)
     * @Serializer\Groups({"listingDetail", "eventDetail"})
     */
    private $unsplash;

    /**
     * @var string
     *
     * @Serializer\Groups({"ManageCategories"})
     */
    private $url;

    /**
     * @var integer
     *
     * @ORM\Column(name="wp_event_id", type="integer", nullable=true)
     */
    private $wpEventId;

    /**
     * @var integer
     *
     * @ORM\Column(name="wp_post_id", type="integer", nullable=true)
     */
    private $wpPostId;

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
     * Set type
     *
     * @param string $type
     * @return Image
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return Image
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set unsplash
     *
     * @param string $unsplash
     * @return Image
     */
    public function setUnsplash($unsplash)
    {
        $this->unsplash = $unsplash;

        return $this;
    }

    /**
     * Get unsplash
     *
     * @return string
     */
    public function getUnsplash()
    {
        return $this->unsplash;
    }

    /**
     * @param $url
     * @return Image
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return int
     */
    public function getWpEventId()
    {
        return $this->wpEventId;
    }

    /**
     * @param int $wpEventId
     */
    public function setWpEventId($wpEventId)
    {
        $this->wpEventId = $wpEventId;
    }

    /**
     * @return int
     */
    public function getWpPostId()
    {
        return $this->wpPostId;
    }

    /**
     * @param int $wpPostId
     */
    public function setWpPostId($wpPostId)
    {
        $this->wpPostId = $wpPostId;
    }
}
