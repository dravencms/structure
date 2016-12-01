<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;

use Salamek\Cms\Models\IMenuContent;

class MenuContent implements IMenuContent
{
    /** @var \Dravencms\Model\Structure\Entities\MenuContent */
    private $menuContent;

    /**
     * MenuContent constructor.
     * @param \Dravencms\Model\Structure\Entities\MenuContent $menuContent
     */
    public function __construct(\Dravencms\Model\Structure\Entities\MenuContent $menuContent)
    {
        $this->menuContent = $menuContent;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->menuContent->getParameters();
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return new Menu($this->menuContent->getMenu());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->menuContent->getId();
    }

    /**
     * @return string
     */
    public function getFactory()
    {
        return $this->menuContent->getFactory();
    }

}