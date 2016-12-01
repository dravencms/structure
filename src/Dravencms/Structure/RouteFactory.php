<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Dravencms\Base\IRouterFactory;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Nette\Application\Routers\RouteList;
use Salamek\Cms\SlugRouter;

/**
 * Class RouteFactory
 * @package Salamek\Cms
 */
class RouteFactory implements IRouterFactory
{
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
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();

        $router[] = $frontEnd = new RouteList('Front');

        $frontEnd[] = new SlugRouter('[<locale [a-z]{2}>/][<slug .*>]', $this->structureMenuRepository);

        //$frontEnd[] = new Route('[<locale [a-z]{2}>/]<presenter>/<action>[/<id [0-9]+>]', []);

        return $router;
    }
}