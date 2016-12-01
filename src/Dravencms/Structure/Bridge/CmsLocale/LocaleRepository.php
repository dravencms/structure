<?php

namespace Dravencms\Structure\Bridge\CmsLocale;

use Salamek\Cms\Models\ILocaleRepository;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class LocaleRepository implements ILocaleRepository
{
    /** @var \Dravencms\Model\Locale\Repository\LocaleRepository  */
    private $localeRepository;

    /**
     * LocaleRepository constructor.
     * @param \Dravencms\Model\Locale\Repository\LocaleRepository $localeRepository
     */
    public function __construct(\Dravencms\Model\Locale\Repository\LocaleRepository $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * @return \Generator
     */
    public function getActive()
    {
        foreach ($this->localeRepository->getActive() AS $row)
        {
            yield new Locale($row);
        }
    }

    /**
     * @return Locale
     */
    public function getCurrentLocale()
    {
        return new Locale($this->localeRepository->getCurrentLocale());
    }

    /**
     * @return Locale
     */
    public function getDefault()
    {
        return new Locale($this->localeRepository->getDefault());
    }
}