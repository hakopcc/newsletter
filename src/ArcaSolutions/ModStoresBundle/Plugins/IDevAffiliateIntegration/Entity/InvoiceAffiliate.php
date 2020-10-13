<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\IDevAffiliateIntegration\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Example
 *
 * @ORM\Table(name="Invoice_Affiliate")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\IDevAffiliateIntegration\Repository\InvoiceAffiliateRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InvoiceAffiliate
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
     * @ORM\Column(name="invoice_id", type="integer", nullable=false)
     */
    private $invoiceId;

    /**
     * @var string
     *
     * @ORM\Column(name="affiliate", type="string", length=255, nullable=false)
     */
    private $affiliate;

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
     * @return string
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param string $invoiceId
     * @return InvoiceAffiliate
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * @param string $affiliate
     * @return InvoiceAffiliate
     */
    public function setAffiliate($affiliate)
    {
        $this->affiliate = $affiliate;

        return $this;
    }
}