<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

trait TCms
{
    /** @var Cms */
    private $cms;

    /**
     * @param Cms $cms
     */
    public function injectCms(Cms $cms)
    {
        $this->cms = $cms;
    }
}