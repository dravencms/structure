<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Structure;

use App\Model\Structure\Entities\Menu;

interface MenuFormFactory
{
    /**
     * @param Menu|null $parentMenu
     * @param Menu|null $menu
     * @return MenuForm
     */
    public function create(Menu $parentMenu = null, Menu $menu = null);
}