<?php

namespace Dravencms\Structure\Bridge\CmsLocale;

use Salamek\Cms\Models\ILocale;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class Locale implements ILocale
{
    /** @var \Dravencms\Model\Locale\Entities\Locale  */
    private $locale;

    /**
     * Locale constructor.
     * @param \Dravencms\Model\Locale\Entities\ILocale $locale
     */
    public function __construct(\Dravencms\Model\Locale\Entities\ILocale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->locale->getLanguageCode();
    }
}