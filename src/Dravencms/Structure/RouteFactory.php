<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Dravencms\Base\IRouterFactory;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Nette\Application\Routers\RouteList;
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
     * @param LocaleRepository $localeRepository
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

        $frontEnd[] = new SlugRouter('[<locale='.$this->localeRepository->getDefault()->getLanguageCode().' [a-z]{2}>/][<slug .*>]', $this->structureMenuRepository, $this->menuTranslationRepository, $this->localeRepository);

        return $router;
    }
}