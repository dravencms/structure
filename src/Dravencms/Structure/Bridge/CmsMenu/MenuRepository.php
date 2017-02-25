<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;

use Dravencms\Model\Locale\Repository\LocaleRepository;
use Nette;
use Salamek\Cms\Models\ILocale;
use Salamek\Cms\Models\IMenu;
use Salamek\Cms\Models\IMenuRepository;

class MenuRepository implements IMenuRepository
{
    /** @var \Dravencms\Model\Structure\Repository\MenuRepository */
    private $menuRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /**
     * MenuRepository constructor.
     * @param \Dravencms\Model\Structure\Repository\MenuRepository $menuRepository
     * @param LocaleRepository $localeRepository
     */
    public function __construct(\Dravencms\Model\Structure\Repository\MenuRepository $menuRepository, LocaleRepository $localeRepository)
    {
        $this->menuRepository = $menuRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @param $id
     * @param ILocale|null $locale
     * @return Menu
     */
    public function getOneById($id, ILocale $locale = null)
    {
        if (!is_null($locale)) {
            $nativeLocale = $this->localeRepository->getOneByLanguageCode($locale->getLanguageCode());
        } else {
            $nativeLocale = null;
        }

        return new Menu($this->menuRepository->getOneById($id, $nativeLocale));
    }

    /**
     * @param $presenter
     * @param $action
     * @return Menu
     */
    public function getOneByPresenterAction($presenter, $action)
    {
        return new Menu($this->menuRepository->getOneByPresenterAction($presenter, $action));
    }

    /**
     * @param $slug
     * @param array $parameters
     * @param null $locale
     * @return array
     */
    public function getOneBySlug($slug, $parameters = [], $locale = null)
    {
        if (!is_null($locale)) {
            $nativeLocale = $this->localeRepository->getOneByLanguageCode($locale);
        } else {
            $nativeLocale = null;
        }

        list($nativeMenu, $parametersMenu) = $this->menuRepository->getOneBySlug($slug, $parameters, $nativeLocale);

        return [($nativeMenu ? new Menu($nativeMenu) : null), $parametersMenu];
    }

    /**
     * @return \Generator|Menu[]
     */
    public function getAll()
    {
        foreach ($this->menuRepository->getAll() AS $row) {
            yield new Menu($row);
        }
    }

    /**
     * @param $name
     * @param $metaDescription
     * @param $metaKeywords
     * @param $metaRobots
     * @param $title
     * @param $h1
     * @param bool $isActive
     * @param bool $isHidden
     * @param bool $isHomePage
     * @param float $sitemapPriority
     * @param bool $isSitemap
     * @param bool $isShowH1
     * @param null $presenter
     * @param null $action
     * @param bool $isSystem
     * @param array $parameters
     * @param bool $isRegularExpression
     * @param bool $isRegularExpressionMatchArguments
     * @param string $layoutName
     * @return Menu
     */
    public function createNewMenu(
        $name,
        $metaDescription,
        $metaKeywords,
        $metaRobots,
        $title,
        $h1,
        $isActive = true,
        $isHidden = false,
        $isHomePage = false,
        $sitemapPriority = 0.5,
        $isSitemap = true,
        $isShowH1 = true,
        $presenter = null,
        $action = null,
        $isSystem = false,
        array $parameters = [],
        $isRegularExpression = false,
        $isRegularExpressionMatchArguments = false,
        $layoutName = 'layout'
    ) {
        return new Menu($this->menuRepository->createNewMenu($name, $metaDescription, $metaKeywords, $metaRobots, $title, $h1, $isActive, $isHidden, $isHomePage, $sitemapPriority, $isSitemap,
            $isShowH1, $presenter, $action, $isSystem, $parameters, $isRegularExpression, $isRegularExpressionMatchArguments, $layoutName));
    }

    /**
     * @param IMenu $menu
     * @param ILocale $locale
     * @param $name
     * @param $metaDescription
     * @param $metaKeywords
     * @param $title
     * @param $h1
     */
    public function translateMenu(
        IMenu $menu,
        ILocale $locale,
        $name,
        $metaDescription,
        $metaKeywords,
        $title,
        $h1
    ) {

        $nativeMenu = $this->menuRepository->getOneById($menu->getId());

        $nativeLocale = $this->localeRepository->getOneByLanguageCode($locale->getLanguageCode());

        $this->menuRepository->translateMenu($nativeMenu, $nativeLocale, $name, $metaDescription, $metaKeywords, $title, $h1);
    }

    /**
     * @param IMenu $menu
     * @param string $latteTemplate
     */
    public function saveLatteTemplate(IMenu $menu, $latteTemplate)
    {
        $nativeMenu = $this->menuRepository->getOneById($menu->getId());
        $this->menuRepository->saveLatteTemplate($nativeMenu, $latteTemplate);
    }

    /**
     * @param IMenu $menu
     * @param $presenterName
     * @param $actionName
     * @return void
     */
    public function savePresenterAction(IMenu $menu, $presenterName, $actionName)
    {
        $nativeMenu = $this->menuRepository->getOneById($menu->getId());
        $this->menuRepository->savePresenterAction($nativeMenu, $presenterName, $actionName);
    }

    /**
     * @param $factory
     * @param array $parameters
     * @param bool $isSystem
     * @return Menu
     */
    public function getOneByFactoryAndParametersAndIsSystem($factory, array $parameters = [], $isSystem = false)
    {
        return new Menu($this->menuRepository->getOneByFactoryAndParametersAndIsSystem($factory, $parameters, $isSystem));
    }

}