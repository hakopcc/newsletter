<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListingLevelInstantMessenger.
 *
 * @ORM\Table(name="ListingLevel_InstantMessenger")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Repository\ListingLevelInstantMessengerRepository")
 */
class ListingLevelInstantMessenger
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ListingBundle\Entity\ListingLevel", fetch="EAGER")
     * @ORM\JoinColumn(name="level", referencedColumnName="value")
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(name="instant_messenger", type="string", length=1, nullable=false)
     */
    private $instantMessenger = 'n';

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
     * @return ListingLevelInstantMessenger
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     *
     * @return ListingLevelInstantMessenger
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Set detail.
     *
     * @param string $instantMessenger
     *
     * @return ListingLevelInstantMessenger
     */
    public function setInstantMessenger($instantMessenger)
    {
        $this->instantMessenger = $instantMessenger;

        return $this;
    }

    /**
     * Get instant messenger.
     *
     * @return string
     */
    public function getInstantMessenger()
    {
        return $this->instantMessenger;
    }
}
