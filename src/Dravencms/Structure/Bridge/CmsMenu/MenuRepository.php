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
     * @return Menu
     */
    public function getOneById($id)
    {
        return new Menu($this->menuRepository->getOneById($id));
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
     * @return Menu[]
     */
    public function getAll()
    {
        foreach ($this->menuRepository->getAll() AS $row) {
            yield new Menu($row);
        }
    }

    /**
     * @param $identifier
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
        $identifier,
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
        return new Menu($this->menuRepository->createNewMenu($identifier, $isActive, $isHidden, $isHomePage, $sitemapPriority, $isSitemap,
            $isShowH1, $presenter, $action, $isSystem, $parameters, $isRegularExpression, $isRegularExpressionMatchArguments, $layoutName));
    }

    /**
     * @param IMenu $menu
     * @param string $latteTemplate
     * @return void
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
}