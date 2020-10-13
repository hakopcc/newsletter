<?php

namespace ArcaSolutions\ClassifiedBundle\Entity;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Classifiedcategory
 *
 * @ORM\Table(name="ClassifiedCategory", indexes={@ORM\Index(name="category_id", columns={"category_id"}), @ORM\Index(name="title1", columns={"title"}), @ORM\Index(name="friendly_url1", columns={"friendly_url"}), @ORM\Index(name="keywords", columns={"keywords", "title"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\ClassifiedBundle\Repository\ClassifiedcategoryRepository")
 */
class Classifiedcategory extends BaseCategory
{
    /**
     * @var Classifiedcategory
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ClassifiedBundle\Entity\Classifiedcategory", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    protected $children = [];

    /**
     * @var Classifiedcategory
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ClassifiedBundle\Entity\Classifiedcategory", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
}
