<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Example
 *
 * @ORM\Table(name="Module_CategoryIcon")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ModuleCategoryIcon
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
     * @ORM\Column(name="module", type="string", length=10, nullable=true)
     */
    private $module;

    /**
     * @var integer
     *
     * @ORM\Column(name="pin_id", type="integer", nullable=true)
     */
    private $pinId;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ModuleCategoryIcon
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param string $module
     * @return ModuleCategoryIcon
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return int
     */
    public function getPinId()
    {
        return $this->pinId;
    }

    /**
     * @param int $pinId
     * @return ModuleCategoryIcon
     */
    public function setPinId($pinId)
    {
        $this->pinId = $pinId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     * @return ModuleCategoryIcon
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }
}