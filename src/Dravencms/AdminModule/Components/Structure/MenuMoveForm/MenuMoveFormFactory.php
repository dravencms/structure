<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Structure\MenuMoveForm;

use Dravencms\Model\Structure\Entities\Menu;

interface MenuMoveFormFactory
{
    /**
     * @param Menu $menu
     * @return MenuMoveForm
     */
    public function create(Menu $menu): MenuMoveForm;
}