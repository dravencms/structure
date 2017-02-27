<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;

use Salamek\Cms\Models\IMenu;

class Menu implements IMenu
{
    /** @var \Dravencms\Model\Structure\Entities\Menu */
    private $menu;

    /**
     * Menu constructor.
     * @param \Dravencms\Model\Structure\Entities\Menu $menu
     */
    public function __construct(\Dravencms\Model\Structure\Entities\Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->menu->getAction();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->menu->getId();
    }

    /**
     * @return mixed|string
     */
    public function getLatteTemplate()
    {
        return $this->menu->getLatteTemplate();
    }

    /**
     * @return mixed|string
     */
    public function getLayoutName()
    {
        return $this->menu->getLayoutName();
    }

    /**
     * @return \Generator|MenuContent
     */
    public function getMenuContents()
    {
        foreach ($this->menu->getMenuContents() AS $row)
        {
            yield new MenuContent($row);
        }
    }

    /**
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->menu->getMetaRobots();
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->menu->getParameters();
    }

    /**
     * @return string
     */
    public function getPresenter()
    {
        return $this->menu->getPresenter();
    }

    /**
     * @return bool
     */
    public function isHomePage()
    {
        return $this->menu->isHomePage();
    }

    /**
     * @return bool|mixed
     */
    public function isShowH1()
    {
        return $this->menu->isShowH1();
    }

    /**
     * @return bool
     */
    public function isSystem()
    {
        return $this->menu->isSystem();
    }

    public function getIdentifier()
    {
        return $this->menu->getIdentifier();
    }
}