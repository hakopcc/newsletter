<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Example
 *
 * @ORM\Table(name="Setting_Navigation_DropdownMenu")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Repository\DropdownMenuRepository")
 * @ORM\HasLifecycleCallbacks
 */
class SettingNavigationDropdownMenu
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="parent_menu", type="integer", length=1, nullable=true)
     */
    private $parentMenu;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SettingNavigationDropdownMenu
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getParentMenu()
    {
        return $this->parentMenu;
    }

    /**
     * @param string $parentMenu
     * @return SettingNavigationDropdownMenu
     */
    public function setParentMenu($parentMenu)
    {
        $this->parentMenu = $parentMenu;

        return $this;
    }
}