<?php

namespace ArcaSolutions\ArticleBundle\Entity;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Articlecategory
 *
 * @ORM\Table(name="ArticleCategory", indexes={@ORM\Index(name="category_id", columns={"category_id"}), @ORM\Index(name="title1", columns={"title"}), @ORM\Index(name="friendly_url1", columns={"friendly_url"}), @ORM\Index(name="keywords", columns={"keywords", "title"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\ArticleBundle\Repository\ArticlecategoryRepository")
 */
class Articlecategory extends BaseCategory
{
    /**
     * @var Articlecategory
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ArticleBundle\Entity\Articlecategory", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    protected $children = [];

    /**
     * @var ArticleCategory
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ArticleBundle\Entity\Articlecategory", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
}
