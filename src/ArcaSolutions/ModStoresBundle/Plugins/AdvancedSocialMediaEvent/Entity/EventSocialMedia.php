<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedSocialMediaEvent\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventSocialMedia
 *
 * @ORM\Table(name="Event_SocialMedia")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class EventSocialMedia
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
     * @ORM\OneToOne(targetEntity="ArcaSolutions\EventBundle\Entity\Event", fetch="EAGER")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var integer
     *
     * @ORM\Column(name="import_id", type="integer", nullable=true)
     */
    private $importId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", nullable=true)
     */
    private $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter", type="string", nullable=true)
     */
    private $twitter;

    /**
     * @var string
     *
     * @ORM\Column(name="instagram", type="string", nullable=true)
     */
    private $instagram;

    /**
     * @var string
     *
     * @ORM\Column(name="temp", type="string", nullable=false)
     */
    private $temp;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return EventSocialMedia
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     * @return EventSocialMedia
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return int
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * @param int $importId
     * @return EventSocialMedia
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     * @return EventSocialMedia
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     * @return EventSocialMedia
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstagram()
    {
        return $this->instagram;
    }

    /**
     * @param string $instagram
     * @return EventSocialMedia
     */
    public function setInstagram($instagram)
    {
        $this->instagram = $instagram;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * @param string $temp
     * @return EventSocialMedia
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;

        return $this;
    }
}