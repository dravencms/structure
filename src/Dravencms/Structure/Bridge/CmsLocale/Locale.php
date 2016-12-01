<?php

namespace Dravencms\Structure\Bridge\CmsLocale;

use Salamek\Cms\Models\ILocale;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class Locale implements ILocale
{
    private $locale;

    public function __construct(\Dravencms\Model\Locale\Entities\Locale $locale)
    {
        $this->locale = $locale;
    }

    public function getLanguageCode()
    {
        return $this->locale->getLanguageCode();
    }
}