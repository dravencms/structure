<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Dravencms\Base\IRouterFactory;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Nette\Application\Routers\RouteList;
#use Salamek\Cms\SlugRouter;
use Dravencms\Model\Structure\Repository\MenuRepository;

/**
 * Class RouteFactory
 * @package Salamek\Cms
 */
class RouteFactory implements IRouterFactory
{
    /** @var MenuRepository @inject */
    private $structureMenuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var LocaleRepository */
    private $localeRepository;
    
    /**
     * RouteFactory constructor.
     * @param MenuRepository $structureMenuRepository
     * @param MenuTranslationRepository $menuTranslationRepository
     */
    public function __construct(
        MenuRepository $structureMenuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        LocaleRepository $localeRepository
    )
    {
        $this->structureMenuRepository = $structureMenuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();

        $router[] = $frontEnd = new RouteList('Front');

        $frontEnd[] = new SlugRouter('[<locale [a-z]{2}>/][<slug .*>]', $this->structureMenuRepository, $this->menuTranslationRepository, $this->localeRepository);

        //$frontEnd[] = new Route('[<locale [a-z]{2}>/]<presenter>/<action>[/<id [0-9]+>]', []);

        return $router;
    }
}