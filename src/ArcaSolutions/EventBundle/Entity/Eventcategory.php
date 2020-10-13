<?php

namespace ArcaSolutions\EventBundle\Entity;

use ArcaSolutions\WebBundle\Entity\BaseCategory;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use Doctrine\ORM\Mapping as ORM;

/**
 * Eventcategory
 *
 * @ORM\Table(name="EventCategory", indexes={@ORM\Index(name="category_id", columns={"category_id"}), @ORM\Index(name="title1", columns={"title"}), @ORM\Index(name="friendly_url1", columns={"friendly_url"}), @ORM\Index(name="keywords", columns={"keywords", "title"})})
 * @ORM\Entity(repositoryClass="ArcaSolutions\EventBundle\Repository\EventcategoryRepository")
 */
class Eventcategory extends BaseCategory
{
    /**
     * @var ImportLog
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ImportBundle\Entity\ImportLog")
     * @ORM\JoinColumn(nullable=true, name="import_id")
     */
    private $import;

    /**
     * @var Eventcategory
     *
     * @ORM\OneToMany(targetEntity="ArcaSolutions\EventBundle\Entity\Eventcategory", mappedBy="parent", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    protected $children = [];

    /**
     * @var Eventcategory
     *
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\EventBundle\Entity\Eventcategory", inversedBy="children", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @return ImportLog
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param ImportLog $import
     * @return $this
     */
    public function setImport($import)
    {
        $this->import = $import;

        return $this;
    }
}
