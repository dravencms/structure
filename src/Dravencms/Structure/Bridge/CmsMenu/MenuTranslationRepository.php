<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;

use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Nette;
use Salamek\Cms\Models\ILocale;
use Salamek\Cms\Models\IMenu;
use Salamek\Cms\Models\IMenuTranslationRepository;

class MenuTranslationRepository implements IMenuTranslationRepository
{
    /** @var \Dravencms\Model\Structure\Repository\MenuRepository */
    private $menuTranslationRepository;

    /** @var MenuRepository */
    private $menuRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /**
     * MenuTranslationRepository constructor.
     * @param \Dravencms\Model\Structure\Repository\MenuTranslationRepository $menuTranslationRepository
     * @param MenuRepository $menuRepository
     * @param LocaleRepository $localeRepository
     */
    public function __construct(
        \Dravencms\Model\Structure\Repository\MenuTranslationRepository $menuTranslationRepository,
        MenuRepository $menuRepository,
        LocaleRepository $localeRepository
    )
    {
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->menuRepository = $menuRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @param IMenu $menu
     * @param ILocale|null $locale
     * @return MenuTranslation
     */
    public function getOneByMenu(IMenu $menu, ILocale $locale = null)
    {
        $nativeMenu = $this->menuRepository->getOneById($menu->getId());
        $nativeLocale= $this->localeRepository->getOneByLanguageCode($locale->getLanguageCode());
        return new MenuTranslation($this->menuTranslationRepository->getTranslation($nativeMenu, $nativeLocale));
    }

    /**
     * @param $slug
     * @param array $parameters
     * @param ILocale|null $locale
     * @return MenuTranslation
     */
    public function getOneBySlug($slug, $parameters = [], ILocale $locale = null)
    {
        $nativeLocale = $this->localeRepository->getOneByLanguageCode($locale->getLanguageCode());
        return new MenuTranslation($this->menuTranslationRepository->getOneBySlug($slug, $parameters, $nativeLocale));
    }
}