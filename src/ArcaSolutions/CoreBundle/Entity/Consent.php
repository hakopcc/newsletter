<?php

namespace ArcaSolutions\CoreBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Consent
 *
 * @ORM\Table(name="Consent")
 * @ORM\Entity
 */
class Consent
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;
    /**
     * @ORM\OneToMany(targetEntity="AccountConsent", mappedBy="consent_id",fetch="EAGER")
     **/
    protected $accountConsent;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return Consent
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getAccountConsent()
    {
        return $this->accountConsent;
    }

    /**
     * @param mixed $accountConsent
     */
    public function setAccountConsent(AccountConsent $accountConsent): void
    {
        $this->accountConsent = $accountConsent;
    }

}
