<?php

namespace Dravencms\Structure\Bridge\CmsLocale;

use Salamek\Cms\Models\ILocaleRepository;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class LocaleRepository implements ILocaleRepository
{
    
    public function __construct(\Dravencms\Model\Locale\Repository\LocaleRepository $localeRepository)
    {
    }


    public function getActive()
    {
        // TODO: Implement getActive() method.
    }

    public function getCurrentLocale()
    {
        // TODO: Implement getCurrentLocale() method.
    }

    public function getDefault()
    {
        // TODO: Implement getDefault() method.
    }
}