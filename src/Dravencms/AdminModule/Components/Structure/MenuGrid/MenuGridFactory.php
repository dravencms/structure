<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Structure\MenuGrid;

use Dravencms\Model\Structure\Entities\Menu;

interface MenuGridFactory
{
    /**
     * @param Menu $parentMenu
     * @return MenuGrid
     */
    public function create(Menu $parentMenu = null);
}