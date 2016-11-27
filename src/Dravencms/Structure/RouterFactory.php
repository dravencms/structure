<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Dravencms\Model\Structure\Repository\MenuRepository;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Salamek\Cms\SlugRouter;

/**
 * Class RouteFactory
 * @package Salamek\Cms
 */
class RouterFactory
{
    /** @var array */
    private $routeFactories = [];

    /** @var MenuRepository @inject */
    private $structureMenuRepository;

    /**
     * RouterFactory constructor.
     * @param MenuRepository $structureMenuRepository
     */
    public function __construct(MenuRepository $structureMenuRepository)
    {
        $this->structureMenuRepository = $structureMenuRepository;
    }

    /**
     * @param IRouterFactory $routeFactory
     */
    public function addRouteFactory(IRouterFactory $routeFactory)
    {
        $this->routeFactories[] = $routeFactory;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();

        $router[] = $frontEnd = new RouteList('Front');

        $frontEnd[] = new SlugRouter('[<locale [a-z]{2}>/][<slug .*>]', $this->structureMenuRepository);

        //$frontEnd[] = new Route('[<locale [a-z]{2}>/]<presenter>/<action>[/<id [0-9]+>]', []);

        foreach ($this->routeFactories AS $routeFactory) {
            $router[] = $routeFactory->createRouter();
        }

        return $router;
    }
}