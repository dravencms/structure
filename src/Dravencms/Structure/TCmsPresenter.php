<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;


use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use WebLoader\Nette\LoaderFactory;

use Dravencms\Locale\CurrentLocaleResolver;

trait TCmsPresenter
{
    /** @var Structure */
    private $structure;

    /** @var MenuRepository */
    private $menuRepository;

    /** @var CurrentLocaleResolver */
    private $currentLocaleResolver;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /**
     * @param Structure $structure
     */
    public function injectCms(Structure $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @param MenuRepository $menuRepository
     */
    public function injectMenuRepository(MenuRepository $menuRepository): void
    {
        $this->menuRepository = $menuRepository;
    }

    /**
     * @param LoaderFactory $webLoader
     */
    public function injectLoaderFactory(LoaderFactory $webLoader): void
    {
        $this->webLoader = $webLoader;
    }

    /**
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function injectCurrentLocaleResolver(CurrentLocaleResolver $currentLocaleResolver): void
    {
        $this->currentLocaleResolver = $currentLocaleResolver;
    }

    /**
     * @param MenuTranslationRepository $menuTranslationRepository
     */
    public function injectMenuTranslationRepository(MenuTranslationRepository $menuTranslationRepository): void
    {
        $this->menuTranslationRepository = $menuTranslationRepository;
    }

    public final function renderDefault(): void
    {
        $menu = $this->menuRepository->getOneById($this->menuId);
        $this->setLayout($menu->getLayoutName());


        $translated = $this->menuTranslationRepository->getTranslation($menu, $this->currentLocaleResolver->getCurrentLocale());

        $this->template->identifier = $menu->getIdentifier();
        $this->template->metaDescription = $translated->getMetaDescription();
        $this->template->title = $translated->getTitle();
        $this->template->metaKeywords = $translated->getMetaKeywords();
        $this->template->metaRobots = $menu->getMetaRobots();
        $this->template->h1 = $translated->getH1();
        $this->template->showH1 = $menu->isShowH1();
        $this->template->bodyClass = ($menu->isHomePage() ? 'homepage': 'subpage');
        $this->template->isHomePage = $menu->isHomePage();
    }

    /**
     * Formats layout template file names.
     * @return array
     */
    public final function formatLayoutTemplateFiles(): array
    {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':' . $name, ':'));
        $className = trim(str_replace($presenter . 'Presenter', '', get_class($this)), '\\');
        $exploded = explode('\\', $className);
        $moduleName = str_replace('Module', '', end($exploded));
        $layout = $this->layout ? $this->layout : 'layout';
        $dir = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
        $list = parent::formatLayoutTemplateFiles();
        do {
            $list[] = $this->structure->getLayoutDir()."/@$layout.latte";
            $dir = dirname($dir);
        } while ($dir && ($name = substr($name, 0, intVal(strrpos($name, ':')))));
        return $list;
    }
    
    /**
     * @param $name
     * @param array $parameters
     * @return mixed
     */
    public function cmsLink(string $name, array $parameters = []): string
    {
        $menu = $this->structure->findComponentActionPresenter($name, $parameters);
        return $this->link($menu->getPresenter().':'.$menu->getAction());
    }

    /**
     * @param $name
     * @param array $parameters
     */
    public function cmsRedirect(string $name, array $parameters = []): string
    {
        $menu = $this->structure->findComponentActionPresenter($name, $parameters);
        $this->redirect($menu->getPresenter().':'.$menu->getAction());
    }
}
