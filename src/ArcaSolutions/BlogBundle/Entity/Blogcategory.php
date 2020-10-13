<?php

namespace ArcaSolutions\BlogBundle\Entity;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Blogcategory
 *
 * @ORM\Table(name="BlogCategory", indexes={@ORM\Index(name="category_id", columns={"category_id"}),@ORM\Index(name="title1", columns={"title"}), @ORM\Index(name="friendly_url1", columns={"friendly_url"}), @ORM\Index(name="level", columns={"level"}), @ORM\Index(name="keywords", columns={"keywords", "title"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\BlogBundle\Repository\BlogcategoryRepository")
 */
class Blogcategory extends BaseCategory
{
    /**
     * @var Blogcategory
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\BlogBundle\Entity\Blogcategory", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    protected $children = [];

    /**
     * @var Blogcategory
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\BlogBundle\Entity\Blogcategory", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var integer
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level = '0';

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\BlogBundle\Entity\BlogCategory1", mappedBy="category")
     */
    private $blogCategory;

    /**
     * Set level
     *
     * @param integer $level
     * @return Blogcategory
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $blogCategory
     * @return Blogcategory
     */
    public function setBlogCategory($blogCategory)
    {
        $this->blogCategory = $blogCategory;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getBlogCategory()
    {
        return $this->blogCategory;
    }
}
