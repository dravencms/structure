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
        return $this->structureMenuFrontFactory->create();
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