<?php

namespace ArcaSolutions\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccountConsent
 *
 * @ORM\Table(name="Account_Consent")
 * @ORM\Entity
 */
class AccountConsent
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="accountConsent")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $account_id;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Consent", inversedBy="accountConsent")
     * @ORM\JoinColumn(name="consent_id", referencedColumnName="id", nullable=false)
     */
    private $consent_id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * @return mixed
     */
    public function getConsentId()
    {
        return $this->consent_id;
    }




    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return AccountConsent
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $account_id
     */
    public function setAccountId($account_id): void
    {
        $this->account_id = $account_id;
    }

    /**
     * @param int $consent_id
     */
    public function setConsentId($consent_id): void
    {
        $this->consent_id = $consent_id;
    }


}
