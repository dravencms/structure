<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Nette\Application\Routers\RouteList;

/**
 * Class RouteFactory
 * @package Salamek\Cms
 */
class RouterFactory
{
    /** @var array */
    private $routeFactories = [];

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
        foreach ($this->routeFactories AS $routeFactory) {
            $router[] = $routeFactory->createRouter();
        }

        return $router;
    }
}