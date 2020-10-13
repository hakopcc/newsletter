<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionCategory
 *
 * @ORM\Table(name="QuestionCategory")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Repository\QuestionCategoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class QuestionCategory extends BaseCategory
{
    /**
     * @var QuestionCategory
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\QuestionCategory", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    protected $children = [];

    /**
     * @var QuestionCategory
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\QuestionCategory", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
}
