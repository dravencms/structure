<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\FrontModule;


use Dravencms\FrontModule\Components\Locale\Locale\Switcher\SwitcherFactory;
use Dravencms\FrontModule\Components\Structure\Menu\Breadcrumb\BreadcrumbFactory;
use Dravencms\FrontModule\Components\Structure\Menu\Front\FrontFactory;
use Dravencms\FrontModule\Components\Structure\Menu\Special\SpecialFactory;
use Dravencms\Locale\TLocalizedPresenter;
use Salamek\Cms\Cms;

abstract class SlugPresenter extends BasePresenter
{
    use TLocalizedPresenter;
    
    /** @var Cms @inject */
    public $cmsFactory;

    /** @var SwitcherFactory @inject */
    public $localeLocaleSwitcherFactory;

    /** @var FrontFactory @inject */
    public $structureMenuFrontFactory;

    /** @var SpecialFactory @inject */
    public $structureMenuSpecialFactory;

    /** @var BreadcrumbFactory @inject */
    public $structureMenuBreadcrumbFactory;

    /** @var \Dravencms\FrontModule\Components\Structure\Search\Bar\BarFactory @inject */
    public $structureSearchBarFactory;

    /**
     * @return Components\Structure\Search\Bar\Bar
     */
    public function createComponentStructureSearchBar()
    {
        return $this->structureSearchBarFactory->create();
    }

    /**
     * @return Components\Structure\Menu\Breadcrumb\Breadcrumb
     */
    public function createComponentStructureMenuBreadcrumb()
    {
        return $this->structureMenuBreadcrumbFactory->create();
    }

    /**
     * @return Components\Structure\Menu\Special\Special
     */
    public function createComponentStructureMenuSpecial()
    {
        return $this->structureMenuSpecialFactory->create();
    }


    /**
     * @return Components\Structure\Menu\Front\Front
     */
    public function createComponentStructureMenuFront()
    {
        $menuConfig = [
            'decorate' => true,
            'rootOpen' => function ($tree) {
                if (count($tree) && ($tree[0]['lvl'] == 0)) {
                    return '<ul class="clearlist">';
                } else {
                    return '<ul class="mn-sub" id="menu-item-'.$tree[0]['lvl'].'">';
                }
            },
            'rootClose' => function ($tree) {
                if (count($tree) && ($tree[0]['lvl'] == 0)) {
                    return '</ul>';
                } else {
                    return '</ul>';
                }
            },
            'childOpen' => function ($tree) {
                $active = false;
                if (array_key_exists('__children', $tree) && count($tree['__children'])) {
                    foreach ($tree['__children'] AS $child) {
                        if ($this->isLinkCurrent($child['presenter'].':'.$child['action'])) {
                            $active = true;
                        }
                    }
                } else {
                    $active = $this->isLinkCurrent($tree['presenter'].':'.$tree['action']);
                }
                return '<li class="'.($tree['lvl'] == 0 ? 'nav-item' : '').' ' . ($active ? 'active ' : '') . (!empty($tree['__children']) ? 'has-submenu': '') . '">';
            },
            'childClose' => '</li>',
            'nodeDecorator' => function ($node) {
                if (is_null($node['translations'][0]['customUrl']))
                {
                    $url = $this->link($node['presenter'].':'.$node['action']);
                }
                else
                {
                    $url = $node['translations'][0]['customUrl'];
                }
                return '<a href="' . (!empty($node['__children']) && !$node['isContent'] ? '#' : $url) . '" '.(!empty($node['__children']) ? '' : '').(!is_null($node['target']) ? ' target="'.$node['target'].'"' : '').'>' . $node['translations'][0]['name'] . ' ' . (!empty($node['__children']) ? '<span class="caret"></span>' : '') . '</a>';
            }
        ];
        return $this->structureMenuFrontFactory->create($menuConfig);
    }

    /**
     * @return Components\Locale\Locale\Switcher\Switcher
     */
    public function createComponentLocaleLocaleSwitcher()
    {
        return $this->localeLocaleSwitcherFactory->create();
    }

    /**
     * @return \WebLoader\Nette\CssLoader
     */
    public function createComponentCss()
    {
        return $this->webLoader->createCssLoader($this->getLayout() ? $this->getLayout(): $this->cmsFactory->getDefaultLayout());
    }

    /**
     * @return \WebLoader\Nette\JavaScriptLoader
     */
    public function createComponentJs()
    {
        return $this->webLoader->createJavaScriptLoader($this->getLayout() ? $this->getLayout(): $this->cmsFactory->getDefaultLayout());
    }

}