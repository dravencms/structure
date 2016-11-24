<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;


class MenuParameterSumGenerator
{
    public function hash(array $parameters)
    {
        ksort($parameters);
        return md5(json_encode($parameters));
    }
}