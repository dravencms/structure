<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Dravencms\Structure\Structure;

trait TCms
{
    /** @var Structure */
    private $structure;

    /**
     * @param Structure $structure
     */
    public function injectCms(Structure $structure)
    {
        $this->structure = $structure;
    }
}