<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;

use Nette;
use Salamek\Cms\Models\ILocale;
use Salamek\Cms\Models\IMenu;
use Salamek\Cms\Models\IMenuTranslationRepository;

class MenuTranslationRepository implements IMenuTranslationRepository
{
    /** @var \Dravencms\Model\Structure\Repository\MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var MenuRepository */
    private $menuRepository;

    /** @var \Dravencms\Model\Locale\Repository\LocaleRepository */
    private $localeRepository;

    /**
     * MenuTranslationRepository constructor.
     * @param \Dravencms\Model\Structure\Repository\MenuTranslationRepository $menuTranslationRepository
     * @param \Dravencms\Model\Structure\Repository\MenuRepository $menuRepository
     * @param \Dravencms\Model\Locale\Repository\LocaleRepository $localeRepository
     */
    public function __construct(
        \Dravencms\Model\Structure\Repository\MenuTranslationRepository $menuTranslationRepository,
        \Dravencms\Model\Structure\Repository\MenuRepository $menuRepository,
        \Dravencms\Model\Locale\Repository\LocaleRepository $localeRepository
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
        $natimeTranslation = $this->menuTranslationRepository->getTranslation($nativeMenu, $nativeLocale);
        return ($natimeTranslation ? new MenuTranslation($natimeTranslation) : null);
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
        list($nativeTranslation, $parameters) = $this->menuTranslationRepository->getOneBySlug($slug, $parameters, $nativeLocale);
        return ($nativeTranslation ? new MenuTranslation($nativeTranslation) : null);
    }

    /**
     * @param IMenu $menu
     * @param ILocale $locale
     * @param $h1
     * @param $metaDescription
     * @param $metaKeywords
     * @param $title
     * @param $name
     * @param $slug
     * @return void
     */
    public function translateMenu(IMenu $menu, ILocale $locale, $h1, $metaDescription, $metaKeywords, $title, $name, $slug = null)
    {
        $nativeMenu = $this->menuRepository->getOneById($menu->getId());
        $nativeLocale= $this->localeRepository->getOneByLanguageCode($locale->getLanguageCode());
        $this->menuTranslationRepository->translateMenu($nativeMenu, $nativeLocale, $h1, $metaDescription, $metaKeywords, $title, $name, $slug);
    }
}