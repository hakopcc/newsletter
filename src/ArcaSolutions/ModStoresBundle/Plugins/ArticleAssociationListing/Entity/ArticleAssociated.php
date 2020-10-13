<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArticleAssociated
 *
 * @ORM\Table(name="ArticleAssociated")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ArticleAssociated
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
     * @ORM\Column(name="listing_id", type="integer", nullable=true)
     */
    private $listingId;

    /**
     * @ORM\OneToOne(targetEntity="ArcaSolutions\ArticleBundle\Entity\Article", fetch="EAGER")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ArticleAssociated
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getListingId()
    {
        return $this->listingId;
    }

    /**
     * @param mixed $listingId
     * @return ArticleAssociated
     */
    public function setListing($listingId)
    {
        $this->listingId = $listingId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param mixed $article
     * @return ArticleAssociated
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }
}